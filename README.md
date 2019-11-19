## Simple personnel management application 
### Overview

Manage your employees, their salary payments and skills. Gain useful insight into various skills and their value.

#### Employee management
You can add, update and delete your employees. Each employee has basic contact information, can have multiple addresses, a history of salary payments and list of skills.

#### Payroll management
History of salary payments can be managed for each employee on the employee edit page. Each payment has one primary skill. Payment with a primary skill selected will be included in stats of that skill - the min, max and avg payment amount. It is currently assumed that payments occur on monthly basis.

#### Skill management
Define skills, which can then be assigned to your employees. Each skill has 5 levels.

* Beginner
* Intermediate
* Advanced
* Expert
* Master

Minimum, maximum and average salary is the calculated for each skill and skill level.

For example, we can then observe that skill Java at level Master has a higher maximum salary then PHP at the same skill level.

#### Future development

##### Project management
Define projects, and assign them employees. Each project can be in one of the following states: Scheduled, In Production, Completed

### How to install for development

#### Prerequisites

* docker (required, tested on 18.*)
* docker-compose (required, tested on 1.21.*)
* make (recommended)

#### Installation steps

1) Clone the repository
2) Copy docker-compose.override.yml.dist to docker-compose.override.yml
3) Install php dependencies with composer with `make composer`
4) Install javascript dependencies with composer with `make react-install`
5) In project repository, start the project containers with `docker-compose up -d`
6) Run database migrations with `make migrate`
7) Import database fixtures with `make fixtures`
8) Setup elastic mapping with `make elastic-setup-mapping`
9) Export employees to elastic with `make export-employees-to-elastic`
9) Trigger stats recalculation for all skills with `make recalculate-all-skills`
10) Run tests with `make test`
11) In a new console, start watching for changes in react `make react-watch`

Make use of Makefile commands to set up the application

#### Recommended IDE

PHP Storm with Symfony plugin installed and enabled

#### Contributing

Please make sure you conform to the coding standards before committing

Following tools are used to maintain coding standards:

* php-cs-fixer
* eslint

Also, keep the README.md and CHANGELOG.md to date.

Before committing, stop watching for changes in react, and then compile javascript entries with `make react-build`.

#### Tips for developers

These commands may be useful when setting up new entities and related boilerplate code:
```
bin/console make:entity
bin/console make:crud
```

Remember to keep the doctrine schema valid: `bin/console doctrine:schema:validate`

New migrations can be generated via: `bin/console doctrine:migrations:diff`
