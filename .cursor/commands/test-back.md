# Run Backend Tests

Execute the Laravel test suite for zonix-eats-back.

## Instructions

1. From the project root (`zonix-eats-back`), run: `php artisan test`
2. If the user specified a filter (e.g. "OrderTest"), run: `php artisan test --filter=<FilterName>`
3. Report the result: number of tests run, passed, failed, and any failure output.
4. If tests fail, suggest or apply fixes only if the user asked to fix them; otherwise just report.

## Notes

- Use `--filter=NombreTest` for a single test class.
- Use `php artisan test --coverage` only if the user asks for coverage.
