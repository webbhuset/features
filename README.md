# Features

This module is a collection of features missing in Magento.

## Installation

If you are using composer composer, just add this to you `composer.json` file.
```json
    "require": {
        "magento-hackathon/magento-composer-installer": "*",
        "webbhuset/features": "*"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/magento-hackathon/magento-composer-installer"
        },
        {
            "type":"vcs",
            "url":"https://github.com/webbhuset/features.git"
        }
    ],
```

You can also copy the files manually to your Magento Project.

## Configuration

Some general settings can be found in __System > Configuration > Webbhuset > Features__

## List of features

### Column added to the Category Products Grid

Added product type filter (simle, configurable etc.) column to category products grid in __Catalog > Manage Categories__.

### Random order on Products in Category

This feature updates the category product position to a random order. For the effect to be visible on the site, you need to set categeory sort by to position (Best Value).

There are two options for how to perform the shuffle:

* Shuffle the position of all products in a store every night.
* Shuffle product position on a specific category, just press "Shuffle Product Order" under __Catalog > Manage Categories > Category Products__

__Important!__ You need to update the _category products_ index after changing product position. 
