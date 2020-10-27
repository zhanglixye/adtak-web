#!/bin/bash

set -x

# 業務idから,ファイル名とフォルダ名を生成する
create_lower_file_name_and_capital_folder(){
  result_array[0]=`printf B%05d $1`
  result_array[1]=`printf b%05d $1`
  echo ${result_array[*]}
}

business_id=$1
compare_branch=$2

echo "チェックをはじめます。"
result=($(create_lower_file_name_and_capital_folder ${business_id}))
#echo ${result[@]}
> modified_file_list.txt
git diff origin/${compare_branch} HEAD^ --stat --name-only > ./modified_file_list.txt
cnt=0
err_count=0
while read line
do
  cnt=`expr $cnt + 1`

  case ${line} in
    # フォルダ名は大文字
    "admonster_app/app/Http/Controllers/Api/Biz/${result[0]}/"* | \
    "admonster_app/app/Http/Controllers/Biz/${result[0]}/"* | \
    "admonster_app/resources/assets/js/components/Molecules/Biz/${result[0]}/"* | \
    "admonster_app/resources/assets/js/components/Organisms/Biz/${result[0]}/"* | \
    "admonster_app/resources/assets/js/components/Templates/Biz/${result[0]}/"* | \
    "admonster_app/resources/assets/js/components/Atoms/Biz/${result[0]}/"* | \
    "admonster_app/resources/assets/js/mixins/Biz/${result[0]}/"* | \
    "admonster_app/resources/assets/js/stores/Biz/${result[0]}/"*)
      ;;
    # フォルダ名は小文字
    "admonster_app/public/images/biz/${result[1]}/"* | \
    "admonster_app/storage/biz/${result[1]}/"* | \
    "admonster_app/resources/views/biz/${result[1]}/"*)
      ;;
    # ファイル名は固定
    "admonster_app/config/biz/${result[1]}.php" | \
    "admonster_app/database/seeds/${result[0]}Seeder.php" | \
    "admonster_app/resources/assets/sass/biz/${result[1]}.scss" | \
    "admonster_app/resources/lang/en/biz/${result[1]}.php" | \
    "admonster_app/resources/lang/ja/biz/${result[1]}.php" | \
    "admonster_app/routes/biz/${result[1]}.php")
      ;;
    # 業務開発で各共通フォルダー
    "admonster_app/app/Http/Controllers/Api/Biz/Common/"* | \
    "admonster_app/app/Services/CommonMail/"* | \
    "admonster_app/routes/biz/common/"* | \
    "admonster_app/resources/assets/js/components/Atoms/Biz/Common/"* | \
    "admonster_app/resources/assets/js/components/Molecules/Biz/Common/"* | \
    "admonster_app/resources/assets/js/components/Organisms/Biz/Common/"*)
      ;;
    *)
      err_count=`expr ${err_count} + 1`
      echo "${line}"
  esac
done < ./modified_file_list.txt
rm ./modified_file_list.txt
if [[ ${err_count} -gt 0 ]]; then
  echo "業務開発で変更できないファイルが${err_count}件検出されました。業務開発ではこのファイルは変更できません。"
  exit 1
else
  exit 0
fi
