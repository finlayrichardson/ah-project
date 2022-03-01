# interpret task and student id from args
# move the specified file into the synced folder
# change the .replit to have that file name
  # replit has language and run that syncs from github and runs file
# commit and push
# potentially introduce multiple so teachers using at same time don't mess it up

$task_id = $1;
$student_id = $2;
$file = `ls ../code/$task_id/$student_id`;
rm -rf ../current-code/{*,.*};
mv ../code/$task_id/$student_id/$file ../current-code/;
echo "language=python3
      run=git pull;
      python3 $file;" > ../current-code/.replit;
