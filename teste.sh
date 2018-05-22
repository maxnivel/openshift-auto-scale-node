#!/bin/bash
cd $( cd "$(dirname "$0")" ; pwd -P )

echo $(pwd)

bash ./build.sh

s2i build https://github.com/maxnivel/openshift-auto-scale-node.git --context-dir=web   maxnivel/openshift-auto-scale-node-plataform:latest openshift-auto-scale-node
docker run -p 8080:8080 openshift-auto-scale-node