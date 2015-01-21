# Hook Documentation

This module export all hooks present in a specified template. It is used to generate the [documentation](http://doc.thelia.net/en/documentation/modules/hooks/hooks_list.html)

Only a command is available in this module.

## Installation

with composer : 

```
"require": {
    "thelia/hook-doc-module": "~1.0"
}
```

## Usage

```
$ php Thelia hook:export-list
```

#### Arguments : 

- template : it is the template id definition : 
    - 1 : front-office (default)
    - 2 : back-office
    - 3 : pdf
    - 4 email
    
exemple : 
    
```
$ php Thelia hook:export-list 2 \\export hooks for current activated back-office template
```

#### Options

- format : export format wanted : json, xml, yml or array (default: "json")
- order :  hooks order by file

exemple :

```
$ php Thelia hook:export-list 3 -f json -o \\ export hooks in PDF template in json format ordered by file name
```