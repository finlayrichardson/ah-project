#!/bin/bash

task_id=$1
student_id=$2
git -C ../current-code pull origin master
rm -rf ../current-code/* 2>/dev/null

file=`ls ../code/$task_id/$student_id`
cp ../code/$task_id/$student_id/$file ../current-code/;
echo -e "#!/bin/bash\npython3 $file" > ../current-code/runner.sh
echo -e "language='python3'\nrun='git pull && chmod +x runner.sh && clear && ./runner.sh;'" > ../current-code/.replit
git -C ../current-code add .
git -C ../current-code commit -m "Change current file"
git -C ../current-code push https://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@github.com/codecanopy-csprojects/current-code.git
