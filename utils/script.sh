#!/bin/bash

task_id=$1
student_id=$2
file=`ls ../code/$task_id/$student_id`
rm -rf ../current-code/{*,.*} 2>/dev/null
cp ../code/$task_id/$student_id/$file ../current-code/;
echo -e "language=python3\nrun=git pull;\npython3 $file;" > ../current-code/.replit
cd ../current-code
git pull
git add .
git commit -m "Change current file"
git push https://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@github.com/codecanopy-csprojects/current-code.git
