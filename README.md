## LAME

LAME is very stupid programming language with the ability to compile simple programs for 32/64-bit Windows OS.
To increase the degree of idiocy LAME is written in php, maybe later it will be overwritten on Go.


### Features:

for now parser understands only one pseudo-command: "result(NUMBER)", which means
"compile me analog this C program, please":

	int main(void)
	{
		return NUMBER;
	}

* TODO: variables and math operations
* Its all =)


### Compile and run (need installed php-cli):

	php cl.php SOURCE_FILE EXECUTABLE_FILE

	// for check return value (if return not 0):
	start /wait EXECUTABLE_FILE.exe
	echo %errorlevel%

### Info

1. Сканирование исходника и разбиение его на элементарные токены, не зависящие от соседних.
2. Структурирование токенов парсером (БНФ)
3. Объединение несколько подряд идущих токенов в осмысленные конструкции.
4. Замена осмысленных конструкций на соответствующие им машинные команды.
На этом этапе возможны всевозможные интеллектуальные оптимизации.
5. Сборка исполняемого файла
