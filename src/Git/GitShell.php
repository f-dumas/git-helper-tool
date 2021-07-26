<?php

namespace FDTool\GitChecker\Git;

class GitShell
{
    public static function isMasterBranch(string $gitProjectPath): bool
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && (git branch | grep -F '*' | awk '{print $2}')", $gitProjectPath)
            )
        );
        return ($result === "master");
    }

    public static function hasModifiedFiles(string $gitProjectPath): bool
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && git status | grep modif", $gitProjectPath)
            )
        );
        return !empty($result);
    }

    public static function hasAddedFiles(string $gitProjectPath): bool
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && git status | grep new", $gitProjectPath) //  "nouveau"
            )
        );
        return !empty($result);
    }

    public static function hasDeletedFiles(string $gitProjectPath): bool
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && git status | grep Removed", $gitProjectPath) //  "supprimé"
            )
        );
        return !empty($result);
    }

    public static function hasUntrackedFiles($gitProjectPath): bool
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && git status | grep untracked", $gitProjectPath) //  "non suivis"
            )
        );
        return !empty($result);
    }

    public static function executeGitCleanIgnoredFiles(): ?string
    {
        return shell_exec("git clean -Xf");
    }

    public static function executeGitCleanUntrackedFiles(): ?string
    {
        return shell_exec("git clean -df");
    }

    public static function removeMergedBranches()
    {
        $result = shell_exec("$(git branch --merged | egrep -v '(^\*|master)')");

        return $result;



        // Add an option to clean also the merged branches
        /*
         * git-clean-br() {
              for br in $(git branch --merged | egrep -v '(^\*|master)'); do
                git branch -d ${br};
              done
              git fetch --prune
            }
         */
    }
}
