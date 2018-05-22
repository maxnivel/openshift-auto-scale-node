#!/bin/bash
cd $( cd "$(dirname "$0")" ; pwd -P )

echo $(pwd)

docker build -t maxnivel/openshift-auto-scale-node .
if ! [ "$?" = "0" ]
then
 echo "Deu problema na criação da imagem"
 exit 1
fi

#docker rm -f openshift-auto-scale-node

echo ""
echo ""
docker run -it --rm --name openshift-auto-scale-node \
-v $(pwd):/var/www/ \
-w /var/www/ \
-p 8080:80 \
maxnivel/openshift-auto-scale-node $1