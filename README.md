# Web Console

## Coding Style
Please confirm these settings are applied before merging

* use big_cat_head instead of other naming conventions, in PHP/HTML/JavaScript
  * For CSS, use big-cat-head
* Use 4 spaces indention, never use tab
* Use `func() {}` instead of `func(){}`
* Use "" in PHP strings
* Use '' in JavaScript strings
* Use `abc = 123` instead of `abc=123`, except for bash script
* 

## GIT Configuration
Use **input** for **core.autocrlf**
```
$ git config --global core.autocrlf input
```

>  If you’re on a Linux or macOS system that uses LF line endings,
>  then you don’twant Git to automatically convert them when you check out files;
>  however, if a file with CRLF endings accidentally gets introduced,
>  then you may want Git to fix it. You can tell Git to convert CRLF to LF on
> commit but not the other way around by setting core.autocrlf to input:
>   
> https://git-scm.com/book/en/v2/Customizing-Git-Git-Configuration