Collapsing Book Navigation 8.x-7.x
---------------

### About this Module
Converts the standard page-rendered **Book Navigation** block into a fully collapsible tree menu.

### Goals
Provide users with a method to easily traverse large/complex book structures.

### Requirements
During development, this module will require the use of [FontAwesome](https://fontawesome.com/) for icons and [Bootstrap Collapse](https://getbootstrap.com/docs/4.3/components/collapse/) for toggle states.

### Installation
- **Option 1**. Using [Drush](https://github.com/drush-ops/drush#readme)
  ```$ drush en collapsing_book_navigation```

- **Option 2**. Manual Install
  - Download and place this module inside your Drupal */modules* folder.
  - Enable the module in *admin/modules*.

### Usage
Enable this block in:
- Admin > Structure > Block layout > `[Block Region]` > (Place block) > Book Navigation - Collapsible.

The default `[Block Region]` in Bartik is *sidebar_first*.

### Additional Features
This module also supports the ability to create *one* or *many* block regions for books as well as filter out the books to be displayed in each region.
