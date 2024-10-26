# Frame

this is a homework branch

## Installation to run locally

1. Clone the repository
2. run `docker compose up -d`
3. check `http://localhost/home`

### Database

1. run migrations by running `php frame migrate`
2. Enable seeder by uncommenting making the `if` on line 13 a truly state in `src/index.php`
3. Disable seeder again after seeding the database by uncommenting making the `if` on line 13 a truly state in `src/index.php`