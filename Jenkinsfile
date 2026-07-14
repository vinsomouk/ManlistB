pipeline {
    agent {
        label 'Agent_Manlist_Back'
    }

    options {
        skipDefaultCheckout(true)
        disableConcurrentBuilds()
        timestamps()
    }

    environment {
        DOCKER_IMAGE = 'vmk700/manlist-back'

        TEST_DB_CONTAINER = 'manlist-postgres-test'
        TEST_DB_PORT = '55432'

        DATABASE_URL = 'postgresql://test:test@host.docker.internal:55432/manlist?serverVersion=14&charset=utf8'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Check Environment') {
            steps {
                sh '''
                    php --version
                    composer --version
                    docker --version
                    docker info
                '''
            }
        }

        stage('Install') {
            steps {
                sh '''
                    composer install \
                        --no-interaction \
                        --prefer-dist \
                        --no-progress
                '''
            }
        }

        stage('Validate') {
            steps {
                sh 'composer validate --no-check-publish'
                sh 'php bin/console lint:container --env=test'
            }
        }

        stage('Start Test Database') {
            steps {
                sh '''
                    docker rm -f "${TEST_DB_CONTAINER}" 2>/dev/null || true

                    docker run -d \
                        --name "${TEST_DB_CONTAINER}" \
                        -e POSTGRES_USER=test \
                        -e POSTGRES_PASSWORD=test \
                        -e POSTGRES_DB=manlist_test \
                        -p "${TEST_DB_PORT}:5432" \
                        postgres:14

                    echo "Attente du démarrage de PostgreSQL..."

                    for i in $(seq 1 30); do
                        if docker exec "${TEST_DB_CONTAINER}" \
                            pg_isready -U test -d manlist_test
                        then
                            echo "PostgreSQL est prêt."
                            exit 0
                        fi

                        sleep 2
                    done

                    echo "PostgreSQL n'a pas démarré correctement."
                    docker logs "${TEST_DB_CONTAINER}"
                    exit 1
                '''
            }
        }

        stage('Migrations') {
            steps {
                sh '''
                    php bin/console doctrine:migrations:migrate \
                        --env=test \
                        --no-interaction
                '''
            }
        }

        stage('Tests') {
            steps {
                sh 'php bin/phpunit'
            }
        }

        stage('Dependency Security Audit') {
            steps {
                sh '''
                    echo "Audit des dépendances PHP de production..."
                    composer audit --no-dev
                '''
            }
        }

        stage('Scan Docker Configuration') {
            steps {
                sh '''
                    docker run --rm \
                        -v "$PWD:/workspace" \
                        aquasec/trivy:latest config \
                        --severity HIGH,CRITICAL \
                        --exit-code 1 \
                        /workspace
                '''
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    docker.build(
                        "${DOCKER_IMAGE}:${BUILD_NUMBER}",
                        '-f Manlist_Back.Dockerfile .'
                    )
                }
            }
        }

        stage('Scan Docker Image') {
    steps {
        sh '''
            docker run --rm \
                -v /var/run/docker.sock:/var/run/docker.sock \
                -v trivy-cache:/root/.cache/ \
                aquasec/trivy:latest image \
                --severity HIGH,CRITICAL \
                --ignore-unfixed \
                --exit-code 0 \
                "${DOCKER_IMAGE}:${BUILD_NUMBER}"
        '''
    }
}

        stage('Push Registry') {
            steps {
                script {
                    docker.withRegistry(
                        'https://index.docker.io/v1/',
                        'dockerhub-creds'
                    ) {
                        docker.image(
                            "${DOCKER_IMAGE}:${BUILD_NUMBER}"
                        ).push()

                        docker.image(
                            "${DOCKER_IMAGE}:${BUILD_NUMBER}"
                        ).push('latest')
                    }
                }
            }
        }

        stage('Deploy') {
            when {
                expression {
                    return false
                }
            }

            steps {
                echo 'Déploiement à configurer après préparation de la VM'
            }
        }
    }

    post {
        success {
            echo "Pipeline backend réussi : ${DOCKER_IMAGE}:${BUILD_NUMBER}"
        }

        failure {
            echo "Échec du pipeline backend : ${BUILD_URL}"
        }

        always {
            sh '''
                docker rm -f "${TEST_DB_CONTAINER}" 2>/dev/null || true
            '''

            cleanWs()
        }
    }
}