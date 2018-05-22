<?php

class Info {

    static $RATES = [];
    static $RATES_TIME_COUNT_SEC = 300;
    static $RATE_CPU_AVG = 0;
    static $RATES_IN_TIME = [];
    static $MASTER_USER='centos';
    static $MASTER_HOST='master1.openshift-cloud.local';

}

while (true) {

    logme("[" . date('H-i-s') . "] INICIANDO VERIFICAÇÃO");

    //atualizar inventorio de acordo com cluster
    //buscar percentuais no prometheus

    $rateCpu = get_rate_cpu_workers();
    $rateCpuRule = (int) get_max_rate_cpu_rule();

    if ($rateCpuRule <= 0) {
        notificar_administador("[PROBLEMA CONFIGURAÇÃO] rate cpu config menor ou igual a zero: {$rateCpuRule}. Entre no painel administrativo e configure novamente o max cpu para que o cluster escale");
    }

    add_rate_history($rateCpu, 0);

    logme("Max rate: {$rateCpuRule}%");
    logme("CPU do cluster nos ultimos " . Info::$RATES_TIME_COUNT_SEC . " segundos: " . (int) Info::$RATE_CPU_AVG . "%");

    if (Info::$RATE_CPU_AVG >= $rateCpuRule) {
        logme("Adicionar um novo nó ao cluster");
        notificar_administador("CPU do cluster atingiu " . (int) Info::$RATE_CPU_AVG . "% durante " . Info::$RATES_TIME_COUNT_SEC . " segundos. \nUm novo nó será adicionado ao cluster");

        add_node();
    }


    logme("[" . date('H-i-s') . "] FINALIZANDO VERIFICAÇÃO \n\n");

    sleep(20);
}

function add_node() {
    $cmd = cmd("ssh -o \"StrictHostKeyChecking no\" -i ssh/key_master ".Info::$MASTER_USER."@".Info::$MASTER_HOST." \"/home/centos/maxnivel-cloud-openshift/v3.9/add-node.sh -n worker2.openshift-cloud.com\"");

    print_r($cmd);
    
}

function add_rate_history($rateCpu, $rateMemory) {

    logme("add rate history");

    $file = 'database/rates-history.json';
    $rates = (array) json_decode(file_get_contents($file), true);
    $rates[] = ['type' => 'workers', 'cpu' => $rateCpu, 'memory' => $rateMemory, 'time' => time()];
    file_put_contents($file, json_encode($rates));
    load_info_rates();
}

function load_info_rates() {
    logme("load_info_rates");
    $file = 'database/rates-history.json';
    $rates = (array) json_decode(file_get_contents($file));
    Info::$RATES = $rates;
    Info::$RATES_IN_TIME = [];
    $ratesInTime = [];
    $rateAvgTotal = 0;
    foreach ($rates as $rate) {
        if ($rate->time >= time() - Info::$RATES_TIME_COUNT_SEC) {
            $ratesInTime[] = $rate;
            $rateAvgTotal += $rate->cpu;
        }
    }
    $rateAvg = 0;
    if ($ratesInTime) {
        Info::$RATES_IN_TIME = $ratesInTime;
        $rateAvg = $rateAvgTotal / count($ratesInTime);
    }
    Info::$RATE_CPU_AVG = $rateAvg;
}

function get_max_rate_cpu_rule() {
    $rule = get_rule();
    return isset($rule->max_rate_cpu) ? $rule->max_rate_cpu : 70;
}

function get_rule() {
    return json_decode(file_get_contents("database/auto-scale-rules.json"));
}

function get_rate_cpu_workers() {
    return rand(60, 100);
}

function notificar_administador($mensagem) {

    $mensagemSlack = "*OPENSHIFT AUTOSCALE NODE*\n";
    $mensagemSlack .= ">" . $mensagem;
    $slackWebhook = "https://hooks.slack.com/services/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";
    $json = json_encode(['text' => $mensagemSlack]);
    cmd("curl -X POST -H 'Content-type: application/json' --data '{$json}' {$slackWebhook}");
}

function cmd($comando) {
    $saidaArray = array();
    $resultadoExecucao = 0;
    $comando = $comando . " 2> /tmp/error || cat /tmp/error";
    $ultimaLinha = exec($comando, $saidaArray, $resultadoExecucao);

    $retorno = new stdClass();
    $retorno->executou = 0;
    $retorno->mensagem = 'nenhum resultado';
    $retorno->ultimaLinhaMensagem = $ultimaLinha;
    $retorno->mensagemErro = null;
    $retorno->saidaArray = array();

    if (is_array($saidaArray)) {
        $retorno->saidaArray = $saidaArray;
        $retorno->mensagem = implode("\n", $saidaArray);
    }

    if ($resultadoExecucao !== 0) {
        $retorno->executou = 0;
        $retorno->mensagemErro = "\n ATENÇÃO ERRO: não executou o comando '" . $comando . "' com sucesso! {$retorno->mensagem} {$ultimaLinha}";
        return $retorno;
    }

    $retorno->executou = 1;
    return $retorno;
}

function logme($msg) {
    echo " [" . date('Y-m-s') . "] $msg \n";
}
