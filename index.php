<?php
//Это моё, для тестов.
function _pre($ar, $n='')
{
	echo '<pre>'.$n.'<br>';
	if (is_array($ar))
		print_r($ar);
	else
		var_dump($ar);
	echo '</pre>';
}

$example_persons_array = [
	[
		'fullname' => 'Иванов Иван Иванович',
		'job' => 'tester',
	],
	[
		'fullname' => 'Степанова Наталья Степановна',
		'job' => 'frontend-developer',
	],
	[
		'fullname' => 'Пащенко Владимир Александрович',
		'job' => 'analyst',
	],
	[
		'fullname' => 'Громов Александр Иванович',
		'job' => 'fullstack-developer',
	],
	[
		'fullname' => 'Славин Семён Сергеевич',
		'job' => 'analyst',
	],
	[
		'fullname' => 'Цой Владимир Антонович',
		'job' => 'frontend-developer',
	],
	[
		'fullname' => 'Быстрая Юлия Сергеевна',
		'job' => 'PR-manager',
	],
	[
		'fullname' => 'Шматко Антонина Сергеевна',
		'job' => 'HR-manager',
	],
	[
		'fullname' => 'аль-Хорезми Мухаммад ибн-Муса',
		'job' => 'analyst',
	],
	[
		'fullname' => 'Бардо Жаклин Фёдоровна',
		'job' => 'android-developer',
	],
	[
		'fullname' => 'Шварцнегер Арнольд Густавович',
		'job' => 'babysitter',
	],
];

//Разбиение
function getPartsFromFullname($fullName)
{
	$arFullName = explode(' ', $fullName);
	return ['surname' => $arFullName[0], 'name' => $arFullName[1], 'patronomyc' => $arFullName[2]];
}

//Объединение ФИО
function getFullnameFromParts($surname, $name, $patronomyc)
{
	return $surname.' '.$name.' '.$patronomyc;
}

//Сокращенное ФИО
function getShortName($fullName)
{
	$arFullName = getPartsFromFullname($fullName);
	return $arFullName['name'].' '.mb_substr($arFullName['surname'], 0, 1).'.';
}

//Определение пола по ФИО
function getGenderFromName($fullName)
{
	$arFullName = getPartsFromFullname($fullName);
	$iSex = 0;
	//_pre($arFullName, 'fullname');


	//Признаки женского пола
	if (mb_substr($arFullName['patronomyc'], -3, 3) === 'вна') {
		$iSex--;
	}
	if (mb_substr($arFullName['name'], -1, 1) === 'а') {
		$iSex--;
	}
	if (mb_substr($arFullName['surname'], -2, 2) === 'ва') {
		$iSex--;
	}

	//Признаки мужского пола
	if (mb_substr($arFullName['patronomyc'], -2, 2) === 'ич') {
		$iSex++;
	}
	if (mb_substr($arFullName['name'], -1, 1) === 'й'
		|| mb_substr($arFullName['name'], -1, 1) === 'н') {
		$iSex++;
	}
	if (mb_substr($arFullName['surname'], -2, 2) === 'в') {
		$iSex++;
	}

	return $iSex <=> 0;
}

//Определение возрастно-полового состава
function getGenderDescription($arPersons)
{
	$arMale = array_filter($arPersons, 'maleGenderTest');
	$arFemale = array_filter($arPersons, 'femaleGenderTest');
	$arIndeterminate = array_filter($arPersons, 'indeterminateGenderTest');

	//_pre([$arMale,$arFemale,$arIndeterminate]);

	$maleGenderPercent = round(count($arMale)/count($arPersons)*100, 1);
	$femaleGenderPercent = round(count($arFemale)/count($arPersons)*100, 1);
	$indeterminateGenderPercent = round(count($arIndeterminate)/count($arPersons)*100, 1);

	$html = '<div style="background: #F8F8F8; overflow: auto; width: auto; border: solid #D1D9D7; border-width: .1em; padding: .2em .6em;">';
	$html .= '<pre style="margin: 0; line-height: 125%;" class="hljs">';
	$html .= "<span class=\"hljs-title\">Гендерный состав аудитории:\n---------------------------</span>\n";
	$html .= "Мужчины - ${maleGenderPercent}%\nЖенщины - ${femaleGenderPercent}%\nНе удалось определить - ${indeterminateGenderPercent}%";
	$html .= '</pre>';
	$html .= '</div>';

	return $html;
}
//Фильтр по мужскому полу
function maleGenderTest($arPerson)
{
	return getGenderFromName($arPerson['fullname']) === 1;
}
//Фильтр по женскому полу
function femaleGenderTest($arPerson)
{
	return getGenderFromName($arPerson['fullname']) === -1;
}
//Фильтр по неопределенному полу
function indeterminateGenderTest($arPerson)
{
	return getGenderFromName($arPerson['fullname']) === 0;
}

//Идеальный подбор пары
function getPerfectPartner($surname, $name, $patronomyc, $arPersons)
{
	//1. приводим фамилию, имя, отчество (переданных первыми тремя аргументами) к привычному регистру
	$surname = mb_convert_case($surname, MB_CASE_TITLE_SIMPLE);
	$name = mb_convert_case($name, MB_CASE_TITLE_SIMPLE);
	$patronomyc = mb_convert_case($patronomyc, MB_CASE_TITLE_SIMPLE);

	//2. склеиваем ФИО, используя функцию getFullnameFromParts
	$fullName = getFullnameFromParts($surname, $name, $patronomyc);

	//3. определяем пол для ФИО с помощью функции getGenderFromName
	$genderFullName = getGenderFromName($fullName);
	//_pre([$fullName, $genderFullName], 'Входящее');

	$i = 0;
	do {
		//4. случайным образом выбираем любого человека в массиве;
		$arPerson = $arPersons[rand(0, count($arPersons)-1)];

		//5. проверяем с помощью getGenderFromName, что выбранное из Массива ФИО - противоположного пола,
		//если нет, то возвращаемся к шагу 4, если да - возвращаем информацию.
		$genderRandPerson = getGenderFromName($arPerson['fullname']);

		//_pre([$arPerson['fullname'], $genderRandPerson, $i++, $genderFullName === $genderRandPerson || $genderRandPerson === 0], 'Перебор');
		/**
		 * По идее, неопределенный пол также не равен полу
		 */
	} while ($genderFullName === $genderRandPerson /*|| $genderRandPerson === 0*/);


	$html = '<div style="background: #F8F8F8; overflow: auto; width: auto; border: solid #D1D9D7; border-width: .1em; padding: .2em .6em;">';
	$html .= '<pre style="margin: 0; line-height: 125%;" class="hljs">';
	$html .= getShortName($fullName).' + '.getShortName($arPerson['fullname'])." = \n";
	$html .= mb_chr(9825).' Идеально на '.number_format(rand(5000, 10000) / 100, 2, '.', '').'% '.mb_chr(9825);
	$html .= '</pre>';
	$html .= '</div>';

	return $html;
}
?>

<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>SF. Модуль 12. Практическое задание 12.6</title>
	<link rel="stylesheet" href="style.css?<?=filemtime('style.css')?>">
</head>
<body>
	<div class="container">
		<br><br>
		<h2>Определение возрастно-полового состава</h2>
		<?=getGenderDescription($example_persons_array);?>
		<br><br>
		<h2>Идеальный подбор пары</h2>
		<?=getPerfectPartner('Кравец', 'Марина', 'Леонидовна', $example_persons_array)?>
	</div>
</body>
</html>