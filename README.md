# git-project-checker-tool

 Tool to check all your git projects in order to get all your uncommited changes easily.
 
## How to install
 
- Clone the repository on your computer and run:

``` 
composer install
```


##  Commands

### Git Checker

Check if your git repositories are handled correctly:
- check if there are uncommited files
- check if there are repositories not on master branch

Use it with the following command:
 
 ```
 php git-checker [my-root-workspace-containing-git-projects]
 
 #Or run help
 php git-checker  --help
 ```

Example: `php git-checker ~/my-workspace`

## Git Clean

This command will help you clean your repositories by removing the untracked and ignored files.

Use it with the following command:
 
 ```
 php git-clean
 
 #Or run help
 php git-checker  --help
 ```


