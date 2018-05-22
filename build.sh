#indo para a raiz do projeto
cd $( cd "$(dirname "$0")" ; pwd -P )

echo "Iniciando criaçõ da imagem"

docker build -t maxnivel/openshift-auto-scale-node-plataform .

echo "Imagem 'maxnivel/openshift-auto-scale-node' criada com sucesso"
