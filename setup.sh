if [ -z "$BASH_VERSION" ]; then
    echo "\033[0;31m[警告] 請使用 bash 執行本腳本！\033[0m"
    echo "\033[0;33m範例：bash setup.sh\033[0m"
    exit 1
fi
if [ ! -x "$0" ]; then
    echo -e "\033[0;31m[警告] setup.sh 沒有執行權限！\033[0m"
    echo -e "\033[0;33m請執行：chmod +x setup.sh\033[0m"
    exit 1
fi

#!/bin/bash

set -e

# Color https://blog.csdn.net/qq_42372031/article/details/104137272
# 文字顏色
COLOR_RED='\033[0;31m';
COLOR_GREEN='\033[0;32m';
COLOR_YELLOW='\033[0;33m';
COLOR_BLUE='\033[0;34m';
COLOR_PURPLE='\033[38;5;61m';
COLOR_LAVENDER='\033[38;5;183m';
COLOR_REST='\033[0m'; # No Color

# 背景顏色
COLOR_BACKGROUND_RED='\033[0;101m';
COLOR_BACKGROUND_GREEN='\033[1;42m';
COLOR_BACKGROUND_YELLOW='\033[1;43m';
COLOR_BACKGROUND_BLUE_GREEN='\033[46m'; # 青色
COLOR_BACKGROUND_WHITE='\033[47m';

########################################
# 檢查 docker-compose 是否存在
# docker-compose 1.29.0 之後的版本，docker-compose 已經被整合到 docker 中，並改為使用 docker compose
# 這邊使用 command -v docker-compose 來判斷是否存在 docker-compose
# https://stackoverflow.com/questions/66514436/difference-between-docker-compose-and-docker-compose
if command -v docker-compose != NULL; then
    dockerComposeCmd="docker-compose"
else
    dockerComposeCmd="docker compose"
fi

RemoveContainer () {
    lastResult=$?
    if [ $lastResult -ne 0 ] && [ $lastResult -ne 130 ] && [ $lastResult -ne 16888 ]; then
        echo -e "$COLOR_BACKGROUND_RED 狀態:$lastResult, 啟動專案過程有錯誤，移除所有容器。 $COLOR_REST"
        # ${dockerComposeCmd} down
    elif [ $lastResult = 16888 ]; then
        echo -e "$COLOR_BACKGROUND_RED 中止... $COLOR_REST"
        ${dockerComposeCmd} down
    fi
}
trap RemoveContainer EXIT

# 取得資料夾名稱，因資料夾名稱是容器名稱的 prefix
dir=$(pwd)
fullPath="${dir%/}";
containerNamePrefix=${fullPath##*/}
echo -e "$COLOR_BACKGROUND_BLUE_GREEN 現在位置 - ${containerNamePrefix} $COLOR_REST"

# 讀取「.env」
ReadEnv () {
    . ${dir}/.env
}

# 初始化
INIT() {
    # Copy config files
    cp env-sample .env
    cp docker-compose.yml.sample docker-compose.yml
    cp .docker/nginx/default.conf.dist .docker/nginx/conf.d/default.conf

    # 讀取「.env」
    ReadEnv

    # 建立符號連結
    rm -rf ${dir}/web/${PHP_DIRECTORY}/config/.env
    ln ${dir}/.env ${dir}/web/${PHP_DIRECTORY}/config/.env
    echo -e "$COLOR_BACKGROUND_YELLOW 準備啟動檔案... 成功 $COLOR_REST"
}

# 預設設定
DEFAULTSETTING() {
    INIT # 初始化

    # Copy php config files
    cp web/${PHP_DIRECTORY}/config/autoload/development.local.php.dist web/${PHP_DIRECTORY}/config/autoload/development.local.php
    cp web/${PHP_DIRECTORY}/config/autoload/local.php.dist web/${PHP_DIRECTORY}/config/autoload/local.php
    cp web/${PHP_DIRECTORY}/config/autoload/doctrine.local.php.dist web/${PHP_DIRECTORY}/config/autoload/doctrine.local.php
    cp web/${PHP_DIRECTORY}/config/autoload/doctrine-migrations.local.php.dist web/${PHP_DIRECTORY}/config/autoload/doctrine-migrations.local.php
    cp web/${PHP_DIRECTORY}/config/development.config.php.dist web/${PHP_DIRECTORY}/config/development.config.php
    echo -e "$COLOR_BACKGROUND_YELLOW 複製 專案 Config 檔案... 成功 $COLOR_REST"

    # Start container
    ${dockerComposeCmd} up -d --build
    echo -e "$COLOR_BACKGROUND_GREEN 啟動容器... 成功 $COLOR_REST"
}

# 主選單
MAINMENU() {
    # Stern ASCII Art Logo & Info
    echo -e "${COLOR_BLUE}"
    echo "  ██     ██  ███████  ██  ██████   ███████ "
    echo "  ██     ██  ██       ██  ██   ██  ██      "
    echo "  ██  █  ██  █████    ██  ██   ██  █████   "
    echo "  ██ ███ ██  ██       ██  ██   ██  ██      "
    echo "   ███ ███   ███████  ██  ██████   ███████ "
    echo "                                           "
    echo "                                           "
    echo -e "${COLOR_REST}"
    echo -e "${COLOR_YELLOW}         Welcome to Mezzio         ${COLOR_REST}"
    echo -e "${COLOR_GREEN} Version 1.0.0   ©  Mezzio Information.      ${COLOR_REST}"
    echo -e ""
    echo -e "(1) 專案初始化 + 啟動開發環境"
    echo -e "(2) 啟動開發環境"
    echo -e "(3) 執行 ${COLOR_LAVENDER}CLI${COLOR_REST} 工具"
    echo -e "(4) 執行 ${COLOR_LAVENDER}Composer${COLOR_REST}"
    echo -e "(5) 執行 ${COLOR_LAVENDER}Migrate${COLOR_REST}"
    echo -e "(Q) 離開"
    echo -e "───────────────────────────────────────────────"
    echo -e "${COLOR_LAVENDER}▶${COLOR_REST} 請輸入要執行的項目 [1-5] ${COLOR_YELLOW}(預設:${COLOR_GREEN}2${COLOR_YELLOW})${COLOR_REST}"
    echo -en "${COLOR_LAVENDER}➤${COLOR_REST} 選擇："
    read -r user_select
    user_select=${user_select:-2}   # 預設為 2
    user_select_uppercase=$(echo -e "$user_select" | tr '[:upper:]' '[:lower:]')   # 轉換為小寫
    user_select=${user_select:-2}   # 預設為 2
    user_select_uppercase=$(echo -e "$user_select" | tr '[:upper:]' '[:lower:]')   # 轉換為小寫

    ########################################
    # 專案初始化 + 啟動開發環境
    if [ $user_select = 1 ]; then
        #切換 git 分支
        git checkout develop
        echo -e "$COLOR_BACKGROUND_YELLOW 切換至 develop 分支... 成功 $COLOR_REST"

        # Run default setting
        DEFAULTSETTING

        # Install php packages
        docker exec -it mezzio_php_1 composer install && echo -e "$COLOR_BACKGROUND_GREEN 安裝 php 相關套件... 成功 $COLOR_REST"

        # Cache disabled
        docker exec -it mezzio_php_1 composer development-enable && echo -e "$COLOR_BACKGROUND_GREEN 取消 Cache 功能... 成功 $COLOR_REST"

        # Change permission
        sudo chmod 777 -R web/${PHP_DIRECTORY}/data

        MAINMENU # 主選單

        return 0

    ########################################
    # 啟動開發環境
    elif [ $user_select = 2 ]; then
        # Start container
        ${dockerComposeCmd} up -d --build
        echo -e "$COLOR_BACKGROUND_GREEN 啟動容器... 成功 $COLOR_REST"

        # Update php packages
        docker exec -it mezzio_php_1 composer install && echo -e "$COLOR_BACKGROUND_GREEN 更新 php 相關套件... 成功 $COLOR_REST"

        # Cache disabled
        docker exec -it mezzio_php_1 composer development-enable && echo -e "$COLOR_BACKGROUND_GREEN 取消 Cache 功能... 成功 $COLOR_REST"

        MAINMENU # 主選單

        return 0

    ########################################
    # 更新 composer 套件
    elif [ $user_select = 3 ]; then
        # 先執行一次 bin/cli.sh
        docker exec -it mezzio_php_1 bin/cli.sh
        # 互動式 CLI 工具
        while true; do
            echo -e "${COLOR_YELLOW}請輸入要執行的 CLI 指令，或輸入 b 返回主選單：${COLOR_REST}"
            read -p "cli> " cli_cmd
            cli_cmd=$(echo -e "$cli_cmd" | tr '[:upper:]' '[:lower:]')
            if [ "$cli_cmd" = "b" ]; then
                MAINMENU
                return 0
            elif [ -z "$cli_cmd" ]; then
                continue
            else
                docker exec -it mezzio_php_1 bin/cli.sh $cli_cmd
            fi
        done

    elif [ $user_select = 4 ]; then
        # 先執行一次 composer
        docker exec -it mezzio_php_1 composer
        # 互動式 composer 工具
        while true; do
            echo -e "${COLOR_YELLOW}請輸入要執行的 composer 子指令，或輸入 b 返回主選單：${COLOR_REST}"
            read -p "composer> " composer_cmd
            composer_cmd=$(echo -e "$composer_cmd" | tr '[:upper:]' '[:lower:]')
            if [ "$composer_cmd" = "b" ]; then
                MAINMENU
                return 0
            elif [ -z "$composer_cmd" ]; then
                continue
            else
                docker exec -it mezzio_php_1 composer $composer_cmd
            fi
        done

    ########################################
    # Migrate
    elif [ $user_select_uppercase = 5 ]; then
        ReadEnv # 讀取「.env」

        while true; do
            echo -e $COLOR_YELLOW "(1) 執行 workbench export + migrate" $COLOR_REST;
            echo -e $COLOR_YELLOW "(2) 執行 workbench export" $COLOR_REST;
            echo -e $COLOR_YELLOW "(3) 產生 migrate 檔案" $COLOR_REST;
            echo -e $COLOR_YELLOW "(4) 執行 migrate" $COLOR_REST;
            echo -e $COLOR_YELLOW "(5) 還原 migrate" $COLOR_REST;
            echo -e $COLOR_YELLOW "(6) 更新 migrate" $COLOR_REST;
            echo -e $COLOR_YELLOW "(B) 回到主選單" $COLOR_REST;
            read -p "請輸入要執行的項目($(tput setaf 2 )1-5$(tput sgr0)):" migrate_select
            migrate_select_uppercase=$(echo -e "$migrate_select" | tr '[:upper:]' '[:lower:]')   # 轉換為小寫

            if [ "$migrate_select_uppercase" = 1 ]; then
                read -p "$(echo -e $COLOR_GREEN"確定要執行嗎？(yes/no)"$COLOR_REST"["$COLOR_YELLOW"yes"$COLOR_REST"]")" user_confirm
                user_confirm=${user_confirm:-yes}
                user_confirm_uppercase=$(echo -e "$user_confirm" | tr '[:upper:]' '[:lower:]')
                if [ "$user_confirm_uppercase" = 'yes' ]; then
                    docker exec -ti mezzio_php_1 sh bin/export.sh
                    if compgen -G "${dir}/web/${PHP_DIRECTORY}/data/temp/*.php" > /dev/null; then
                        cp ${dir}/web/${PHP_DIRECTORY}/data/temp/*.php ${dir}/web/${PHP_DIRECTORY}/module/Base/src/Entity/
                        rm -f ${dir}/web/${PHP_DIRECTORY}/data/temp/*.php
                    fi
                    docker exec -ti mezzio_php_1 vendor/bin/doctrine migrations:diff
                    docker exec -ti mezzio_php_1 vendor/bin/doctrine migrations:migrate --no-interaction
                    echo -e "$COLOR_BACKGROUND_YELLOW Migrate... 成功 $COLOR_REST"
                fi

                continue
            elif [ "$migrate_select_uppercase" = 2 ]; then
                read -p "$(echo -e $COLOR_GREEN"確定要執行嗎？(yes/no)"$COLOR_REST"["$COLOR_YELLOW"yes"$COLOR_REST"]")" user_confirm
                user_confirm=${user_confirm:-yes}
                user_confirm_uppercase=$(echo -e "$user_confirm" | tr '[:upper:]' '[:lower:]')
                if [ "$user_confirm_uppercase" = 'yes' ]; then
                    rm -f ${dir}/web/${PHP_DIRECTORY}/data/temp/*.php
                    docker exec -ti mezzio_php_1 sh bin/export.sh
                    echo -e "$COLOR_BACKGROUND_YELLOW Migrate... 成功 $COLOR_REST"
                fi

                continue
            elif [ "$migrate_select_uppercase" = 3 ]; then
                read -p "$(echo -e $COLOR_GREEN"確定要執行嗎？(yes/no)"$COLOR_REST"["$COLOR_YELLOW"yes"$COLOR_REST"]")" user_confirm
                user_confirm=${user_confirm:-yes}
                user_confirm_uppercase=$(echo -e "$user_confirm" | tr '[:upper:]' '[:lower:]')
                if [ "$user_confirm_uppercase" = 'yes' ]; then
                    if compgen -G "${dir}/web/${PHP_DIRECTORY}/data/temp/*.php" > /dev/null; then
                        cp ${dir}/web/${PHP_DIRECTORY}/data/temp/*.php ${dir}/web/${PHP_DIRECTORY}/module/Base/src/Entity/
                        rm -f ${dir}/web/${PHP_DIRECTORY}/data/temp/*.php
                    fi
                    docker exec -ti mezzio_php_1 vendor/bin/doctrine migrations:diff
                    echo -e "$COLOR_BACKGROUND_YELLOW Migrate... 成功 $COLOR_REST"
                fi

                continue
            elif [ "$migrate_select_uppercase" = 4 ]; then
                read -p "$(echo -e "請輸入要"$COLOR_YELLOW"migrate"$COLOR_REST"的版本號碼["$COLOR_YELLOW"ex.Version20221202033436"$COLOR_REST"]"):" version_number
                read -p "$(echo -e $COLOR_GREEN"確定要 migrate 嗎？(yes/no)"$COLOR_REST"["$COLOR_YELLOW"yes"$COLOR_REST"]")" user_answer
                user_answer=${user_answer:-yes}
                user_confirm_uppercase=$(echo -e "$user_answer" | tr '[:upper:]' '[:lower:]')
                if [ "$user_confirm_uppercase" = 'yes' ]; then
                    docker exec -ti mezzio_php_1 vendor/bin/doctrine migrations:execute --up "Migrations\\${version_number}"
                    echo -e "$COLOR_BACKGROUND_YELLOW Migrate... 成功 $COLOR_REST"
                fi

                continue
            elif [ "$migrate_select_uppercase" = 5 ]; then
                read -p "$(echo -e "請輸入要"$COLOR_RED"還原"$COLOR_REST"的版本號碼["$COLOR_YELLOW"ex.Version20221202033436"$COLOR_REST"]"):" version_number
                if [ -z "$version_number" ]; then
                    echo -e "$COLOR_BACKGROUND_RED 錯誤!! 請輸入版本號碼 $COLOR_REST"
                else
                    read -p "$(echo -e $COLOR_GREEN"確定要"$COLOR_REST$COLOR_RED"還原"$COLOR_REST $COLOR_GREEN"嗎？(yes/no)"$COLOR_REST"["$COLOR_YELLOW"yes"$COLOR_REST"]")" user_answer
                    user_answer=${user_answer:-yes}
                    user_answer_uppercase=$(echo -e "$user_answer" | tr '[:upper:]' '[:lower:]')
                    if [ "$user_answer_uppercase" = 'yes' ]; then
                        docker exec -ti mezzio_php_1 vendor/bin/doctrine migrations:execute --down "Migrations\\${version_number}"
                        echo -e "$COLOR_BACKGROUND_YELLOW Migrate... 成功 $COLOR_REST"
                    fi
                fi

                continue
            elif [ "$migrate_select_uppercase" = 6 ]; then
                read -p "$(echo -e $COLOR_GREEN"確定要更新 migrate 嗎？(yes/no)"$COLOR_REST"["$COLOR_YELLOW"yes"$COLOR_REST"]")" user_confirm
                user_confirm=${user_confirm:-yes}
                user_confirm_uppercase=$(echo -e "$user_confirm" | tr '[:upper:]' '[:lower:]')
                if [ "$user_confirm_uppercase" = 'yes' ]; then
                    docker exec -ti mezzio_php_1 bin/doctrine.sh migrations:diff

                    # 取得最新的版本號碼
                    version_number=$(docker exec -ti mezzio_php_1 vendor/bin/doctrine migrations:status | grep "Latest Version" | awk '{print $4}')
                    docker exec -ti mezzio_php_1 vendor/bin/doctrine migrations:execute --up "Migrations\\${version_number}"

                    echo -e "$COLOR_BACKGROUND_YELLOW Migrate... 成功 $COLOR_REST"
                fi

                continue
            elif [ "$migrate_select_uppercase" = 'b' ]; then
                MAINMENU # 主選單

                break
            else
                echo -e "$COLOR_BACKGROUND_RED 錯誤!! 請輸入要執行的指令... $COLOR_REST"
                
                continue
            fi
        done

    ########################################
    # 離開
    elif [ "$user_select_uppercase" = 'q' ]; then
        echo -e "$COLOR_BACKGROUND_YELLOW 離開中... $COLOR_REST"

        return 0

    else
        echo -e "$COLOR_BACKGROUND_RED 錯誤!! 請輸入要執行的指令... $COLOR_REST"

        MAINMENU # 主選單

        return 0
    fi
}

MAINMENU # 開始執行主選單
