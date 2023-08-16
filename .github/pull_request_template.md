# Type of pull request

* [ ] Bug fix (involves code and configuration changes)
* [ ] New feature (involves code and configuration changes)
* [ ] Documentation update
* [ ] Something else

# About

*Please describe with a few words what this pull request is about*

# Checklist for code/confirmation changes

*In case you changed the code/configuration, please read each of the following checkboxes as they contain valuable information:*

* [ ] Please add at least **one test case** (unit test, system test, ...) to demonstrate that the change is working. If existing code was changed, your tests cover these code parts as well.
     By the way, you don't have to provide a full fledged PDF file to demonstrate a fix. Instead a unit test is sufficient,
     please have a look at [FontTest](https://github.com/smalot/pdfparser/blob/master/tests/PHPUnit/Unit/FontTest.php#L40) for example code.
     Code changes without any tests are likely to be rejected. If you dont know how to write tests, no problem, tell us upfront and we may add them ourselves or discuss other ways.
* [ ] Please run **PHP-CS-Fixer** before committing, to confirm with our coding styles. See https://github.com/smalot/pdfparser/blob/master/.php-cs-fixer.php for more information about our coding styles.
* [ ] In case you **fix an existing issue**, please do one of the following:
  * [ ] Write in this text something like `fixes #1234` to outline that you are providing a fix for the issue `#1234`.
  * [ ] On the right side in section **Development**, you can also select issues that will be closed after your pull request gets merged. Both ways lead to the same result.
* [ ] In case you changed internal behavior or functionality, please check our documentation to make sure these changes are **documented properly**: https://github.com/smalot/pdfparser/tree/master/doc
* [ ] In case you wanna discuss new ideas/changes and you are not sure, just create a pull request and mark it as **a draft**
      (see [here](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests#draft-pull-requests) for more information).
      This will tell us, that it is not ready for merge, but you want to discuss certain issues.

Pull requests will be declined/rejected if one part of the continous integration pipeline fails. 
We use the pipeline to make sure no regressions are introduced and existing code still runs as expected.
