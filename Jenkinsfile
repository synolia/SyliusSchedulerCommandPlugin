#!/usr/bin/env groovy

@Library("groovyFramework")

import com.synolia.log.Slack;
import com.synolia.system.Security;

// Global
projectName = "Sylius Scheduler Plugin"
srcDir = "src"
binDir = "bin"
applicationDir = "tests/Application"
projectChannelName = "sylius-demo"
lastStage = ""
lastErrorMessage = ""

// Docker
phpDockerRegistry = "registry.synolia.com/php72dev:latest"
dbDockerRegistry = "registry.synolia.com/percona57dev:latest"
seleniumRegistry = "selenium/standalone-chrome:3.141.59-europium"

// Get Slack Client
def slack = new Slack(this, "synolia", projectChannelName)
// Get Security tools
def security = new Security(this)

BUILD_TAG = env.BUILD_TAG.replaceAll('%2F', '_')
JOB_NAME = env.JOB_NAME.replaceAll('%2F', '/')
DB_URL = "mysql://synbshop:Synolia01@db-" + BUILD_TAG + ":3306/sylius-scheduler-plugin"
COMPOSER_ARGS = '-e COMPOSER_HOME=$HOME/.composer -v $HOME/.composer:$HOME/.composer'
PHP_BUILD_ARGS = '--name=php-' + BUILD_TAG

try {
    echo "Installing Selenium container"
    selenium = docker.image(seleniumRegistry)
    // Use the same name for each process: "selenium_chrome" because we don't need multiple seleniums, we can keep it !
    // I don't remove it cause it could be use by several apps...
    selenium.run('--name selenium_chrome --volume /dev/shm:/dev/shm -p 4444:4444')
} catch (Exception e) {
    echo "Container probably already exists... but it's ok ;)"
    echo e.getMessage()
}

pipeline {
    agent any
    options {
        durabilityHint('PERFORMANCE_OPTIMIZED')
    }
    stages {
        stage('Preparing tools') {
            agent {
                docker {
                    image phpDockerRegistry
                    args "${PHP_BUILD_ARGS} -u 0:0 ${COMPOSER_ARGS} --link selenium_chrome:selenium_chrome"
                    reuseNode true
                }
            }
            steps {
                script {
                    lastStage = env.STAGE_NAME

                    try {
                        security.buildSshFolder()

                        // Credentials for Github
                        withCredentials([string(credentialsId: 'githubToken', variable: 'githubToken')]) {
                            sh "composer config -g github-oauth.github.com ${githubToken}"
                        }

                        // Credentials for BitBucket
                        withCredentials([usernamePassword(credentialsId: 'bitbucketToken', passwordVariable: 'token', usernameVariable: 'consumerKey')]) {
                            sh "composer config -g bitbucket-oauth.bitbucket.org ${consumerKey} ${token}"
                        }

                        // Composer install package as global
                        sh 'composer global require hirak/prestissimo ^0.3'
                    } catch (Exception e) {
                        lastErrorMessage = e.message
                        throw e
                    }
                }
            }
        }

        stage('Creating MySQL container') {
            steps {
                script {
                    lastStage = env.STAGE_NAME
                    try {
                        db = docker.image(dbDockerRegistry)
                        db.run('--name db-'+BUILD_TAG)
                    } catch (Exception e) {
                        lastErrorMessage = e.message
                        throw e
                    }
                }
            }
        }

        stage('Application Installation') {
            agent {
                docker {
                    image phpDockerRegistry
                    args "${PHP_BUILD_ARGS} --link db-${BUILD_TAG}:db ${COMPOSER_ARGS}"
                    reuseNode true
                }
            }
            steps {
                script {
                    lastStage = env.STAGE_NAME

                    try {
                        security.buildSshFolder()

                        //.env.test.local not working with behat
                        sh "cp ${applicationDir}/.env ${applicationDir}/.env.test"

                        // Set database to .env
                        sh "echo DATABASE_URL=${DB_URL} >> ${applicationDir}/.env.test"

                        // Composer install
                        sshagent(['deploy_symfony']) {
                            sh "php -d memory_limit=-1 /usr/local/bin/composer install --no-interaction --prefer-dist"
                        }

                        sh "cp behat.yml.dist behat.yml"
                        sh "sed -i 's/localhost:8080/selenium_chrome:4444/g' behat.yml"
                        sh "sed -i 's|{{DB_URL}}|${DB_URL}|g' phpunit.xml.dist"
                        sh "cd ${applicationDir}; yarn install && yarn build"
                        sh "cd ${applicationDir}; php bin/console doctrine:database:create --env=test"
                        sh "cd ${applicationDir}; php bin/console doctrine:schema:create --env=test"
                        sh "cd ${applicationDir}; php bin/console assets:install public --symlink"
                        sh "cd ${applicationDir}; php bin/console cache:warmup --env=test"
                    } catch (Exception e) {
                        lastErrorMessage = e.message
                        throw e
                    }
                }
            }
        }

        stage('Quality Tools') {
            parallel {
                stage('Composer Validation') {
                    agent {
                        docker {
                            image phpDockerRegistry
                            args "${PHP_BUILD_ARGS}-composer ${COMPOSER_ARGS}"
                            reuseNode true
                        }
                    }
                    steps {
                        script {
                            lastStage = env.STAGE_NAME

                            try {
                                sh "composer validate"
                            } catch (Exception e) {
                                lastErrorMessage = e.message
                                throw e
                            }
                        }
                    }
                }

                stage('PhpStan') {
                    agent {
                        docker {
                            image phpDockerRegistry
                            args "${PHP_BUILD_ARGS}-phpstan ${COMPOSER_ARGS}"
                            reuseNode true
                        }
                    }
                    steps {
                        script {
                            lastStage = env.STAGE_NAME

                            try {
                                sh "vendor/bin/phpstan analyse -c phpstan.neon -l max src/"
                            } catch (Exception e) {
                                lastErrorMessage = e.message
                                throw e
                            }
                        }
                    }
                }
            }
        }

        stage('Testing') {
            parallel {
                stage('PhpUnit') {
                    agent {
                        docker {
                            image phpDockerRegistry
                            args "${PHP_BUILD_ARGS}-phpunit ${COMPOSER_ARGS}"
                            reuseNode true
                        }
                    }
                    steps {
                        script {
                            lastStage = env.STAGE_NAME

                            try {
                                sh "vendor/bin/phpunit"
                            } catch (Exception e) {
                                lastErrorMessage = e.message
                                throw e
                            }
                        }
                    }
                }

                stage('PhpSpec') {
                    agent {
                        docker {
                            image phpDockerRegistry
                            args "${PHP_BUILD_ARGS}-phpspec ${COMPOSER_ARGS}"
                            reuseNode true
                        }
                    }
                    steps {
                        script {
                            lastStage = env.STAGE_NAME

                            try {
                                sh "vendor/bin/phpspec run"
                            } catch (Exception e) {
                                lastErrorMessage = e.message
                                throw e
                            }
                        }
                    }
                }

                stage('Behat') {
                    agent {
                        docker {
                            image phpDockerRegistry
                            args "${PHP_BUILD_ARGS}-behat ${COMPOSER_ARGS} --link db-${BUILD_TAG}:db -e APP_ENV=test"
                            reuseNode true
                        }
                    }
                    steps {
                        script {
                            lastStage = env.STAGE_NAME

                            try {
                                sh "vendor/bin/behat --strict -vvv --no-interaction || vendor/bin/behat --strict -vvv --no-interaction --rerun"
                            } catch (Exception e) {
                                lastErrorMessage = e.message
                                throw e
                            }
                        }
                    }
                }
            }
        }
    }
    post {
        success {
            script {
                def message = "SUCCESS :champagne: \n Build <${env.RUN_DISPLAY_URL}|#${env.BUILD_NUMBER}> >> ${JOB_NAME}."
                if (env.CHANGE_URL) {
                    message = message + "\n <${env.CHANGE_URL}|${env.BRANCH_NAME} (${CHANGE_BRANCH})> Let's go for code review."
                }

                slack.send(message, "#14892c")
            }
        }
        unsuccessful {
            script {
                def message = ":warning: ${currentBuild.result}: \n Build <${env.RUN_DISPLAY_URL}|#${env.BUILD_NUMBER}> failed at stage *${lastStage}* >> ${JOB_NAME}."
                if ("" != lastErrorMessage && "script returned exit code 1" != lastErrorMessage) {
                    message = message + "\n```\n${lastErrorMessage}\n```"
                }
                if (env.CHANGE_URL) {
                    message = message + "\n <${env.CHANGE_URL}|${env.BRANCH_NAME} (${CHANGE_BRANCH})> needs to be fixed."
                }
                slack.send(message, "#e01716")
            }
        }
        always {
            cleanWs deleteDirs: true, notFailBuild: true
            sh 'docker rm -f db-'+BUILD_TAG+' || true'
            sh "rm -Rf *"
        }
    }
}
