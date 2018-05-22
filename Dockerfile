FROM centos/php-70-centos7

USER root

RUN wget https://github.com/openshift/origin/releases/download/v3.9.0/openshift-origin-client-tools-v3.9.0-191fece-linux-64bit.tar.gz && \
tar -xvf openshift-origin-client-tools-v3.9.0-191fece-linux-64bit.tar.gz && \
cd  openshift-origin-client-tools-v3.9.0-191fece-linux-64bit && \
cp oc /usr/bin

USER 1001

ENTRYPOINT bash