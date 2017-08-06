help:
	@echo "Please use \`make <target>' where <target> is one of"
	@echo "  test           to perform unit tests. Provide TEST to perform a specific test."
	@echo "  coverage       to perform unit tests with code coverage. Provide TEST to perform a specific test."
	@echo "  sniff          to perform code sniffing. Provide FILE to code sniff a specific test.""
	@echo "  correct        to perform automatic code standard violation corrections."
	@echo "  clean          to remove build artifacts."

test:
	vendor/bin/phpunit ${TEST}

coverage:
	vendor/bin/phpunit --coverage-clover=build/logs/clover.xml ${TEST}

sniff:
	vendor/bin/phpcs ${FILE}

correct:
	vendor/bin/phpcbf

clean:
	rm -rf build/logs
