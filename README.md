# Turnitin
Turnitin Plagiarism Check Web Scrapper in PHP

## Initialization
```
use reyzeal\Turnitin;
$x = new Turnitin('email@something','pwd','path_to_store_session');
```

## Get Classroom
```
// all
$x->classRoom()
// single class
$x->classRoom()->room()  // or
$x->classRoom()->room(0) // by index 0, default = 0

$room = $x->classRoom()->room(0)
$room->name
$room->status
$room->start
$room->end
$room->link
```
## Get Assignment in a classroom
```
$room = $x->classRoom()->room(0)
$assignment = $room -> assignment(1)
$assignment -> title
$assignment -> start
$assignment -> end
$assignment -> link
```

## Get All Paper / Document in a Assignment
```
$assignment = $room -> assignment(1)
$documents = $assignment -> allDocuments()
```

## Upload a document
```
$assignment -> upload(path_to_file, author)
```
