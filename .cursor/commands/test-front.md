# Run Frontend Tests

Execute the Flutter test suite for zonix-eats-front (when workspace includes the frontend or user is in frontend repo).

## Instructions

1. If current workspace is `zonix-eats-front`: run `flutter test`
2. If current workspace is `zonix-eats-back` and user asked for frontend tests: run `flutter test` from the sibling directory `../zonix-eats-front` (or inform the user to open the front workspace).
3. For a specific test file: `flutter test test/path/to/test_file.dart`
4. Report the result: passed/failed count and any failure output.
5. If tests fail, suggest or apply fixes only if the user asked to fix them; otherwise just report.

## Notes

- Run `flutter pub get` first if the user reports dependency or "package not found" issues.
