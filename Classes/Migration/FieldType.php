<?php

namespace WEBcoast\Migrator\Migration;

enum FieldType: string
{
    case TAB = 'tab';
    case SECTION = 'section';
    case CATEGORY = 'category';
    case CHECKBOX = 'checkbox';
    case COLOR = 'color';
    case DATETIME = 'datetime';
    case EMAIL = 'email';
    case FILE = 'file';
    case FLEXFORM = 'flexform';
    case FOLDER = 'folder';
    case GROUP = 'group';
    case IMAGE_MANIPULATION = 'imageManipulation';
    case INLINE = 'inline';
    case JSON = 'json';
    case LANGUAGE = 'language';
    case LEGACY_FILE = 'legacy_file';
    case LINK = 'link';
    case NUMBER = 'number';
    case PASSWORD = 'password';
    case RADIO = 'radio';
    case SELECT = 'select';
    case TEXT = 'input';
    case TEXTAREA = 'text';
    case TREE = 'tree';
    case SLUG = 'slug';
    case UUID = 'uuid';
}
