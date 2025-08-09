// Jenkinsfile
pipeline {
    agent {
        label 'Agent_Manlist_Back'
    }
    
    environment {
        DOCKER_IMAGE = 'manlist-back'
        DOCKER_TAG = "${env.BUILD_NUMBER}"
        DOCKER_REGISTRY = 'your-registry.com'
        DB_HOST = 'postgres-test'
    }
    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        
        stage('Setup Environment') {
            steps {
                sh 'composer install'
                sh 'npm install' // Si utilisation de Node.js
            }
        }
        
        stage('Run Tests') {
            environment {
                DATABASE_URL = "postgresql://test:test@${DB_HOST}:5432/test"
            }
            steps {
                script {
                    // Démarrer PostgreSQL
                    sh 'docker run -d --name ${DB_HOST} -e POSTGRES_USER=test -e POSTGRES_PASSWORD=test -e POSTGRES_DB=test -p 5432:5432 postgres:14'
                    sh 'sleep 10' // Attendre que la DB soit prête
                    
                    // Exécuter les migrations et tests
                    sh 'php bin/console doctrine:database:create --env=test'
                    sh 'php bin/console doctrine:migrations:migrate --env=test -n'
                    sh './bin/phpunit --coverage-html var/report'
                    
                    // Sauvegarder les rapports
                    junit '**/build/junit/*.xml'
                    publishHTML target: [
                        allowMissing: false,
                        alwaysLinkToLastBuild: true,
                        keepAll: true,
                        reportDir: 'var/report',
                        reportFiles: 'index.html',
                        reportName: 'Code Coverage'
                    ]
                }
            }
            post {
                always {
                    // Nettoyer les containers
                    sh 'docker stop ${DB_HOST} || true'
                    sh 'docker rm ${DB_HOST} || true'
                }
            }
        }
        
        stage('Security Scan') {
            steps {
                sh 'composer audit'
                sh 'trivy fs . --severity HIGH,CRITICAL --exit-code 1'
            }
        }
        
        stage('Build Docker Image') {
            steps {
                script {
                    docker.build("${DOCKER_REGISTRY}/${DOCKER_IMAGE}:${DOCKER_TAG}", "-f Manlist_Back.Dockerfile .")
                }
            }
        }
        
        stage('Push to Registry') {
            steps {
                script {
                    docker.withRegistry('https://${DOCKER_REGISTRY}', 'docker-credentials') {
                        docker.image("${DOCKER_REGISTRY}/${DOCKER_IMAGE}:${DOCKER_TAG}").push()
                    }
                }
            }
        }
        
        stage('Deploy to Staging') {
            when {
                branch 'main'
            }
            steps {
                sshagent(['staging-server-credentials']) {
                    sh """
                        ssh user@staging-server '
                            docker pull ${DOCKER_REGISTRY}/${DOCKER_IMAGE}:${DOCKER_TAG}
                            docker stop manlist-back || true
                            docker rm manlist-back || true
                            docker run -d \\
                                --name manlist-back \\
                                -p 80:80 \\
                                -e APP_ENV=prod \\
                                -e DATABASE_URL=postgresql://prod_user:prod_pass@db-prod:5432/prod_db \\
                                ${DOCKER_REGISTRY}/${DOCKER_IMAGE}:${DOCKER_TAG}
                        '
                    """
                }
            }
        }
    }
    
    post {
        always {
            // Nettoyage des images Docker
            sh 'docker system prune -f'
            cleanWs()
        }
        failure {
            slackSend channel: '#alerts', message: "Build ${env.BUILD_NUMBER} failed: ${env.BUILD_URL}"
        }
        success {
            slackSend channel: '#notifications', message: "Build ${env.BUILD_NUMBER} succeeded: ${env.BUILD_URL}"
        }
    }
}