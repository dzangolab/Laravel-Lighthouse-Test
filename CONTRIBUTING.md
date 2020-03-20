# Contributing to the package

First of all, thank you for considering contributing to the package! Please keep in mind the following:

## Workflow

1. Fork the project.
2. Create a new branch for your feature.
3. Add your feature, and be sure to add tests as well. Try to aim for 100% code coverage.
4. Run the tests, make sure they pass and cover all cases, even bizarre ones.
5. Open a pull request and describe your changes.

## Testing

Tests are done using PHPUnit. Simply run `composer test` and the tests should run. Unit tests belong in `tests/Unit` and have the `Tests\Unit` namespace; feature tests belong in `tests/Feature` and have the `Tests\Feature` namespace.