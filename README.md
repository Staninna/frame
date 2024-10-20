# Frame

this is a homework branch

## Installation to run locally

1. Clone the repository
2. run `docker compose up -d`
3. check `http://localhost/home`

### Database

1. Migrations are in `src/migrations.sql` on `mysql://root:root@localhost:3306/test`
2. Enable seeder by uncommenting making the `if` on line 4 truly in `src/index.php`
3. Disable seeder again after seeding the database

I know its scuffed but it works