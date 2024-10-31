# Contributing

**Please read the following text before creating a pull request.**

This project is organized and supported by community contributions and maintenance is done in our sparse time.
We welcome any pull request that contributes to the PDFParser (code, documentation, ...).
However, we would point out that you are initially responsible for a contribution.
If you are new to pull requests see [Github documentation](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests) for further information.
Please don't just throw code at us and expect us to handle it.
That being said, we will assist you and give feedback.

## Important steps

To make life easier for you and us, there is a continuous integration (CI) system that runs software tests and performs a number of other tasks.
The following points describe relevant preparations/inputs for the CI system.
All checks must be green or a pull request is not accepted.

* If you add/change functionality add at least **one test case** (unit test, system test, ...) to demonstrate that your code is working. There is no need to provide a full fledged PDF file to demonstrate a fix. Instead a unit test may be sufficient sometimes, have a look at [FontTest](https://github.com/smalot/pdfparser/blob/master/tests/PHPUnit/Unit/FontTest.php#L40) for example code.
  * :exclamation: **If you dont know how to write a test tell us upfront when opening the pull request and we may add them ourselves or discuss other ways**. This [Medium article](https://pguso.medium.com/a-beginners-guide-to-phpunit-writing-and-running-unit-tests-in-php-d0b23b96749f) might be a good point to start. Code changes without tests are very likely to be rejected.
* Fix reported coding style issues. We use **PHP-CS-Fixer** for that. See https://github.com/smalot/pdfparser/blob/master/.php-cs-fixer.php for more information about our coding styles. See [Developer.md](./doc/Developer.md) for further information.
* In case you **fix an existing issue**, refer to it in the intro text of the pull request. In the following the correct Markdown syntax: `fixes #1234`. This example outlines that you are providing a fix for the issue `#1234`.
* In case **you changed internal behavior/functionality** check our documentation to make sure these changes are **documented properly**: https://github.com/smalot/pdfparser/tree/master/doc
