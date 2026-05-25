# Contributing

**Please read the following text before creating a pull request.**

This project is maintained by the community in very limited time.
**At this point, contributions are effectively limited to:**
- small, well-scoped bug fixes
- PHP compatibility updates
- documentation improvements
- related tests

**Do not** open pull requests for new features, larger refactorings, deep parser changes, performance projects, or broader behavior changes.
Such contributions are unlikely to be reviewed, merged, or maintained responsibly in the current state of the project.

If you are unsure whether a change is small enough in scope, please open an [issue](https://github.com/smalot/pdfparser/issues) first and ask before investing significant time.

If you are new to pull requests, you can find more information in the [GitHub documentation](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests).

Please don't just throw code at us and expect us to handle it.
We will try to give feedback where possible, but contributor support is also limited by time and available project knowledge.

## CI

To make life easier for you and us, there is a Continuous Integration (CI) system that carries out software tests and performs a number of other tasks.
The following points describe the relevant preparations/inputs for the CI system.
All checks must be green, otherwise a pull request will not be accepted.
* If your change goes beyond a small, well-scoped bug fix, please open an [issue](https://github.com/smalot/pdfparser/issues) first to clarify whether it is within the current maintenance scope of this project.
* We only accept code that is bundled with tests. This applies to bug fixes as well as compatibility-related changes. This strengthens the code base and avoids later regressions. :exclamation: **If you don't know how to write a test, tell us upfront when you open the pull request and we might add them ourselves or discuss other ways**. This [Medium article](https://pguso.medium.com/a-beginners-guide-to-phpunit-writing-and-running-unit-tests-in-php-d0b23b96749f) might be a good starting point. Code changes without tests are very likely to be rejected.
* Fix reported issues with the coding style. We use **PHP-CS-Fixer** for this. See [.php-cs-fixer.php](./.php-cs-fixer.php) for more information about our coding styles. [Developer.md](./doc/Developer.md) contains more information about this topic.
* If you are fixing an **existing error**, refer to it in the introduction text of the pull request. For example, if you created a fix for issue `#1234` write the following Markdown: `fixes #1234`.
* If your bug fix or compatibility change affects documented behavior, please update the documentation accordingly: https://github.com/smalot/pdfparser/tree/master/doc
