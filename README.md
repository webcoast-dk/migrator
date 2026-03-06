# Migrator (Core)

This TYPO3 extension helps you migrate content elements from one content type to another, including the migration of the configuration.
The `migrator` extension is only the core part, that provides the basic framework for building and executing migrations.

## Installation

```bash
composer require webcoast/migrator
```

Usually you wouldn't include the `migrator` extension in your project directly, but rather on of the extensions providing content type providers and/or content type builders, which
should have the `migrator` extension as a dependency.

## Compatibility

| Migrator ↓ / TYPO3 → | 13.4 |
|----------------------|:----:|
| 1.0.0                |  ✅   |


## Concept

The extension provides the necessary commands to migrate content element configurations and an upgrade wizard to execute the actual data migration.

The framework uses different components to execute the migrations: Content type providers, content type builders and record data migrators.

### Content Type Providers

Content type providers are responsible for providing a normalized configuration of a content type, that can be used to build a new content type. Furthermore, they
provide the records' data in a normalized way, which is then handed over to the record data migrators, which execute the actual data migration.

As time of writing, there are content type providers for DCE and Flux content elements available in separate extensions. You can easily write your own content type provider for
your custom content types by implementing the `ContentTypeProviderInterface`. Classes implementing this interface are automatically registered as content type providers.

**Important**  
When providing the normalized data for the data migration, data for file fields should be provided as instances of `TYPO3\CMS\Core\Resource\File` for legacy file fields
(type=group and internal_type=file or type=group and internal_type=db), where no file reference record exist yet, and `TYPO3\CMS\Core\Resource\FileReference` for modern
file fields (type=file or type=inline and foreign_table=sys_file_reference), where a file reference record already exists.

### Content Type Builders

Content type builders are responsible for building a new content type configuration based on the normalized configuration provided by a content type provider.
They are part of the interactive migration wizard, which guides you through the process of migrating content type configurations. Each content type builder may
ask the user for additional information, which is necessary to build the new content type configuration. The supported options and source content types may differ
depending on the content type builder. Please see the documentation of the respective content type builders for more information.

As time of writing, there are content type builders for Content Blocks and Container available in separate extensions.

You can write your own content type builder by implementing the `ContentTypeBuilderInterface`. All classes implementing that interface and are automatically registered
as content type builders.

### Record Data Migrators

Record data migrators are responsible for executing the actual data migration. They receive the normalized data of a record provided by a content type provider and must
return an array structure compatible with the TYPO3 DataHandler, which is then used to update the record.

Both data map and command map are used, so you can update the record data, add new records, move records and even translate them, if necessary.

Record data migrations must extend the abstract `RecordDataMigrator` class. All classes extending that class and are automatically registered as record data migrators.

For a more in-depth documentation of the record data migrators, please see the respective section about the [Upgrade Wizard](#upgrade-wizard-record-data-migration) below.

## Commands

The extension provides the following commands:

| Command                       | Description                                                                      |
|-------------------------------|----------------------------------------------------------------------------------|
| `migrator:provider:list`      | List all registered content type providers                                       |
| `migrator:content-types:list` | List all content types provided by the registered content type providers         |
| `migrator:config:from`        | Executes the interactive migration wizard to migrate content type configurations |

They can be executed as Symfony Console commands, like

```bash
./vendor/bin/typo3 {command}
```

## Upgrade Wizard (Record data migration)

The extension provides an upgrade wizard to execute the actual data migration. The upgrade wizard is available in the TYPO3 Install Tool and via the command `upgrade:list`
and `upgrade:run`. The upgrade wizard itself does not execute any migration, but executes all registered record data migrators.

Record data migratiors are classes, that extend the `RecordDataMigrator` class. They must have at least one `SourceContentType` attribute, which defines the source content type(s),
that record data migrator can handle. The `SourceContentType` attribute has two properties: `providerIdentifier` and `contentType`. The `contentType` is used to fetch the
records to migrate, while the `providerIdentifier` is used to get the normalized data of the records, which is then handed over to the `migrate` method of the record data migrator.

**Examples:**

* `#[SourceContentType('dce', 'dce_dceuid12')]` would migrate records with CType `dce_dceuid12` and fetch the normalized data from the content type provider with the identifier `dce`.
* `#[SourceContentType('flux', 'sitepackage_mycontentelement')]` would migrate records with CType `sitepackage_mycontentelement` and fetch the normalized data from the content type provider with the identifier `flux`.

The `migrate` method of the record data migrator receives two parameters: `$incomingData` and `$record`. The `$incomingData` is the normalized data of the record, provided by the
content type provider, while the `$record` is the original record data as an associative array. The `migrate` method must return an associative array, with a structure compatible with
the TYPO3 DataHandler, which is then used to update the record.

**Important:** The returned data must contain the key `CType` with the new content type. It is possible to return different content types with different fields from the same record data
migrator, e.g. depending on a layout field in the source content element. This can be used to split existing content elements into different content types, e.g a content element with
a specific layout is migrated to a new content type, while the other records with the same source content type but different layout are migrated to another content type.

The following example shows a record data migrator, that handles both a DCE and Flux content type and migrates them to a new content type `sitepackage_slider`.
The elements, which may have been a section in the DCE or Flux content element, are migrated to images instead.

```php
<?php

define(strict_types=1);

namespace MyVendor\Sitepackage\Update\RecordDataMigrator;

use WEBcoast\Migrator\Attribute\SourceContentType;
use WEBcoast\Migrator\Update\RecordDataMigrator;

#[SourceContentType('dce', 'dce_dceuid12')]
#[SourceContentType('flux', 'provider_carousel')]
class CarouselMigrator extends RecordDataMigrator
{
    public function migrate(array $incomingData, array $record): array
    {
        $images = [];
        foreach ($incomingData['elements'] as $element) {
            foreach ($element['images'] ?? [] as $image) {
                if ($image instanceof FileReference) {
                    // Update the existing file reference (the field has already been a modern file field, e.g. inline or FAL)
                    $images[] = $this->updateFileReference($image, 'image', [
                        'title' => $element['headline'] ?? '', // Use the null coalescing operator here, to avoid errors in case the field is not filled in the source content element
                        'description' => $element['teaser'] ?? '', // Make sure, you always return a valid data type, e.g. null or an empty string, if the field is not allowed to be null in the database
                    ]);
                } elseif ($image instanceof File) {
                    // Add a new file reference (the field was a legacy file field, e.g. group field with internal type db or internal type file) 
                    $images[] = $this->addFileReference($image, 'tt_content', 'image', $record['uid'], $record['pid'], $record['sys_language_uid'], [
                        'title' => $element['headline'] ?? '', // Use the null coalescing operator here, to avoid errors in case the field is not filled in the source content element
                        'description' => $element['teaser'] ?? '', // Make sure, you always return a valid data type, e.g. null or an empty string, if the field is not allowed to be null in the database
                    ]);
                }
            }
        }
        
        return [
            'CType' => 'sitepackage_slider', // Setting the CType is important, otherwise the record would not be migrated to the new content type
            'header' => $incomingData['headline'] ?? '',
            'bodytext' => $incomingData['teaser'] ?? '',
            'image' => implode(',', $images), // Implode IDs of the file references to a comma-separated string, which is the expected format for file fields in the TYPO3 DataHandler
        ];
    }
}
```

### Migrate files and file references

The abstract `RecordDataMigrator` class provides two helper methods to help migrating files and file references.

When handling files, e.g. from a legacy file field, use

```php
$this->addFileReference($file, $tableName, $fieldName, $recordUid, $recordPid, $sysLanguageUid, $metaData = [])
```

to add a new file reference. The method returns a new id `NEW...`, which must be used in the returned data array to set the new file reference.

When handling file references, e.g. from a modern file field, use

```php
$this->updateFileReference($fileReference, $fieldName, $metaData = [])
```

to update the existing file reference. The method return the uid of the file reference, which must be used in the returned data array to set the updated file reference.

```php
$images = [];
foreach($incomingData['images'] as $image) {
    if ($image instanceof FileReference) {
        $images[] = $this->updateFileReference($image, 'new_image_field');
    } else {if ($image instanceof File) {
        $images[] = $this->addFileReference($image, 'tt_content', 'new_image_field', $record['uid'], $record['pid'], $record['sys_language_uid']);
    }}
}

return [
    'new_image_field' => implode(',', $images), // Implode IDs of the file references to a comma-separated string, which is the expected format for file fields in the TYPO3 DataHandler
];
```

### Migrate sections to inline records

When migrating data from fields, that contain multiple elements, e.g. sections in DCE or Flux content elements, you can use

```php
$this->addReference($tableName, $data)
```

to create a new inline record. The method returns a new id `NEW...`, which must be used in the returned data array to set the new inline record. The `$data` array must contain all
necessary fields for the new inline record, especially the field pointing to the parent record, e.g. `foreign_table_parent_uid` and the `sorting` field, which is used to set the sorting
of the inline records.

```php
$inlineRecords = [];
foreach ($incomingData['sections'] ?? [] as $index => $section) {
    $inlineRecords[] = $this->addReference('new_inline_table', [
        'title' => $section['title'] ?? '', // e.g.
        'foreign_table_parent_uid' => $record['uid'],
        'sorting' => ($index +1),
        'sys_language_uid' => $record['sys_language_uid'],
        'pid' => $record['pid'],
    ]);
}

return [
    'new_inline_field' => implode(',', $inlineRecords), // Implode IDs of the inline records to a comma-separated string, which is the expected format for inline fields in the TYPO3 DataHandler
];
```

### Moving records

To move records, either to another page or after another record, you can use

```php
$this->move($recordUid, $destination, $table = 'tt_content');
```

The `$recordUid` is the uid of the record to move. The `$destination` can either be an integer, string or an array. When destination is a negative integer or a string starting with
`-`, it will be treated as the ID of a record from `$table` and the record will be moved after the record ID of `$destination`. When `$destination` is a positive integer or a string not
starting with `-`, it will be treated as the ID of a page and the record will be moved to that page as the first element.

A special case is when `$destination` is an array with a key `action = paste`, which can be used to move records, while simultaneously updating data, that otherwise would be overriden by
moving the record. You can provide the data to update using the `update` key in the `$destination` array. The actual destination must be provided using the `target` key in the `$destination`
array, which can be either a positive or negative integer or a string, as described above.

### Translating records

To translate records, you can use

```php
$this->localize($recordUid, $languageUid, $table = 'tt_content');
```

where the `$recordUid` is the uid of the record to translate and the `$languageUid` is the uid of the language to translate the record to.

## Sponsors

The development of this extension has been sponsored by

* [Aemka](https://aemka.de/)
* [apart](https://apart.lu/)
* [Homepage Helden](https://www.homepage-helden.de/)
* [HZ Internet Services](https://www.hziegenhain.de/)

Thanks to all sponsors for their support and contributions to the development of this extension!

If you are interested in sponsoring the development of this extension, please contact me via email to [thorben@webcoast.dk](mailto:thorben@webcoast.dk) or in the TYPO3 Slack channel
(#ext-migrator).

## Contributing

Contributions to this extension are always welcome, both in form of pull requests, bug reports and feature requests and ideas.

If you have questions, reach out to me via email to [thorben@webcoast.dk](mailto:thorben@webcoast.dk), the discussion section of this repository or the TYPO3 Slack channel (#ext-migrator).

## License

This extension is licensed under the GPL-3.0 License. See the [LICENSE](LICENSE) file for more details.
