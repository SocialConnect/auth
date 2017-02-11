# Contributing Guide

Hey Contributor! :smiley_cat:

All contributions to SocialConnec are very much encouraged, and we do our best to make it as welcoming and simple as possible.

## Coding Standards

We require that all contributions meet at least the following guidelines:

* Follow PSR-1 & PSR-2
* Use camelCase for variables and methods/functions.
* Don't use functions for casting like `intval`, `boolval` and etc, We are using `(int) $a`.
* Avoid aliases for functions: `sizeof`, `join` and etc.
* Avoid global variables.
* Avoid strict comparisons if not necessary.
* Don't use `Singleton` pattern anywhere.
* Use strict types (objects, arrays); for example: `function testMethod(array $array = [])`.
* Use `$v === null` instead of 'is_null()' for null checking.
* Avoid "Yoda conditions", where constants are placed first in comparisons:

```php
if (true == $someParameter) {
}
```

* Don't forget about empty lines after logical blocks:

```php
public function simpleMethod($a)
{
    $result = 1 + 2;
                                // $result is not related to if block, please write empty line
    $b = $a;
    if ($b) {
        $result = 1;
    }
                                // Empty line is needed there
    return $result;
}
```

### Naming Conventions

#### Naming

* For `abstract` classes, use `Abstract` prefix, `AbstractCondition`
* For `trait`(s), use `Trait` suffix, `ResolveExpressionTrait`
* For `interface`(s), use `Interface` suffix, `PassFunctionCallInterface`
* For any classes that extend from `Exception`, use `Exception` suffix, `UnknownException`

## GIT

Please don't use "merge" in your PR, we are using "rebase", small guide:

[Git Branching Rebasing](https://git-scm.com/book/en/v2/Git-Branching-Rebasing)

Example:

```bash
git checkout YOUR_BRANCH

git fetch upstream

git rebase upstream/master

git push origin YOUR_BRANCH -f
```

This assumes you have configured the upstream remote like this:

```bash
git remote add upstream git@github.com:SocialConnect/auth.git
```

## Testing

```bash
./vendor/bin/phpunit
```

## Maintaining (for push only developers)

- If you are going to close an issue, write a comment describing why you are going to do so (with link reference to the commit/issue/PR)
- Before merge, check that CI passes
- Merge after review (1 other developer reviewed)
- Check that code uses our `Coding Standards` and `Naming Conventions`
- Don't merge big PRs (only simple PRs), if it's a big PR - please ping @ovr
- Write `Thanks` to developer(s) and reviewer(s) after PR was merged
- If there are any `merge` commits in PR, you should write a notice to the submitter to remove those

Thanks :cake:
