#!/bin/bash

business_id=$1
if [[ ! ${business_id} ]]; then
  echo "業務IDを入力してください。"
  exit 1
else
  echo "入力された業務ID:$business_id"
fi

compare_branch="develop"
bash ./file_check.sh ${business_id} ${compare_branch}
check_result=$?
if [[ ${check_result} == 1 ]]; then
  echo "ファイルチェックを失敗しました。ご確認お願い致します。"
elif [[ ${check_result} == 0 ]]; then
  echo "ファイルチェックを成功しました。"
fi
