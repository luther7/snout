help:
	@echo "Please use \`make <target>' where <target> is one of"
	@echo "  test           to perform unit tests. Provide TEST to perform a specific test."
	@echo "  coverage       to perform unit tests with code coverage. Provide TEST to perform a specific test."
	@echo "  clean          to remove build artifacts"

test:
	vendor/bin/phpunit ${TEST}

coverage:
	vendor/bin/phpunit --coverage-clover=build/logs/clover.xml ${TEST}

clean:
	rm -rf build/logs
