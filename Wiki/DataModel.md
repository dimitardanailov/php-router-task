# DataModel

### Table of Contents
  1. [Revision history](#revision-history)
  1. [Database information](#database-information)
  1. [Articles](#articles)

### Revision history

| Date          | Version   | Author      | Revision     |
|:-------------:|:----------|:-----------:|:-------------|
|07 October 2015|0.1|Dimitar Danailov| Document creation and first documentation iteration. Tables - Articles|

**[⬆ back to top](#table-of-contents)**

### Definitions, Acronyms, and Abbreviations

  1. **SYSTEM** - All software components of the the current project as a whole, which are to be programmed by the developers
  1. **USER** - Any individual who interacts with the **SYSTEM**.
  1. **Guest** - A user who does not have a profile.

**[⬆ back to top](#table-of-contents)**

### Database information

#### Multi language / Translation support

Database is not support Multi language.

**[⬆ back to top](#table-of-contents)**

### Articles

This table store information for `Articles`.

| Field         | Data Type       | Constraints | Default value |
| :------------ |:----------------| :-----------| :-------------|
|id|integer|Primary Key|NOT NULL|
|title|varchar(255)|Foreign Key|NOT NULL|
|date|date||NOT NULL|
|text|text||NOT NULL|
|created_at|datetime||NOT NULL|
|updated_at|datetime||NOT NULL|

  1. **id** - the unique ID of the row which is used to access the data.
  1. **title** - title of article.
  1. **date** - for which date is this article
  1. **text** - Article text information. 
  1. **created** - Time and date when the value was first created.
  1. **updated** - Time and date of the last change of field’s value.
  
**[⬆ back to top](#table-of-contents)**
  