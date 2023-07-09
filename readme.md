Тестовое задание для php back-end разработчика

	Задан архив с файлами и папками. 

1. Необходимо программно создать структуру папок:
   /name_project
   /images
   /styleImages
2. Необходимо программно разархивировать файл и переместить данные следующим образом:
   /name_project - здесь поместить файл ...xhtml
   /images – здесь поместить файлы из папки images
   /styleImages – здесь поместить файлы из папки css

3. Далее распарсить данные из файла .xhtml с сохранением вложенности тэгов, сохранения стилей.
4. Из распаршенных данных необходимо создать JSON, следующей структуры:

blocks : {
blockId : - рандомный уникальный id (структуру придумать самому)
html: 		- здесь разместить данные согласно пункта 3, но 					количество текста (за вычетом всех тэгов)  не 						превышало 3000 символов
}

images: {
imageId : рандомный уникальный id (структуру придумать самому)
path : полный путь к файлу
caption: данные согласно пункта 3 (подпись для картинки)
}

tables: {
tableId: рандомный уникальный id (структуру придумать самому)
html: - здесь разместить данные согласно пункта 3 всей таблицы
caption: данные согласно пункта 3 (подпись для таблицы)
}

Примечания:

a. Стили заголовков сохраняем, также сохраняем внутреннюю разбивку текста.

b. Сохраняем структуру списков, цитат,  в одном блоке, независимо от количества символов.

c. Footnotes и References помещаем в отдельный блок, без разбиения по количеству символов. 