help:
	@echo "Please use \`make <target>' where <target> is one of"
	@echo "  test           to perform unit tests. Provide TEST to perform a specific test."
	@echo "  coverage       to perform unit tests with code coverage. Provide TEST to perform a specific test."
	@echo "  coverage-show  to show the code coverage report"
	@echo "  clean          to remove build artifacts"

test:
	vendor/bin/phpunit

coverage:
	vendor/bin/phpunit --coverage-html=build/artifacts/coverage

coverage-show:
	open build/artifacts/coverage/index.html

.PHONY: coverage-show
