<?
$data = json_decode(file_get_contents('php://input'), true);
if (!empty($data['PATH'])) {
    $link = str_replace('.xls', '.php', $data['PATH']);
    print_r(checkCache($link));
}

// Функция для вывода массива в таблицу с добавлением пустых строк
// function renderTable($arSchedule)
// {
//     echo '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">';
//     echo '<thead><tr><th>Время</th><th>Дисциплина</th><th>Ф.И.О Преподавателя</th><th>Аудитория</th></tr></thead>';
//     echo '<tbody>';

//     // Перебор дней недели
//     foreach ($arSchedule['schedule'] as $day => $times) {
//         // Добавляем отступ перед каждым днем
//         echo '<tr><td colspan="4" style="padding:10px; font-weight:bold; background-color:#f0f0f0; text-align: center; font-size: 20px;">' . htmlspecialchars($day) . '</td></tr>';

//         // Для каждого времени занятий
//         foreach ($arSchedule['timeSlots'][$day] as $time) {
//             // Если есть занятия в этот временной интервал
//             if (isset($times[$time])) {
//                 $info = $times[$time];
//                 $rowSpan = count($info['Дисциплина']); // Количество строк для объединения

//                 for ($i = 0; $i < $rowSpan; $i++) {
//                     echo '<tr>';

//                     // Вывод времени только для первой строки этого времени
//                     if ($i == 0) {
//                         echo '<td rowspan="' . $rowSpan . '">' . htmlspecialchars($time) . '</td>';
//                     }

//                     // Вывод дисциплины, преподавателя и аудитории
//                     echo '<td>' . htmlspecialchars($info['Дисциплина'][$i]) . '</td>';
//                     echo '<td>' . htmlspecialchars($info['Ф.И.О Преподавателя'][$i]) . '</td>';
//                     echo '<td>' . htmlspecialchars($info['Аудитория'][$i]) . '</td>';

//                     echo '</tr>';
//                 }
//             } else {
//                 // Если занятий нет, добавляем пустую строку
//                 echo '<tr>';
//                 echo '<td>' . htmlspecialchars($time) . '</td>';
//                 echo '<td colspan="3" style="text-align:center;">-</td>';
//                 echo '</tr>';
//             }
//         }
//     }

//     echo '</tbody>';
//     echo '</table>';
// }
