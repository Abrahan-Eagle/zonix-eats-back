# Create Pull Request

Create a pull request for the current changes on the active branch.

## Instructions

1. Inspect staged and unstaged changes with `git status` and `git diff` (and `git diff --staged` if needed).
2. Write a clear, conventional commit message based on the changes (e.g. `feat(orders): add cancellation timeout`).
3. Stage all intended files with `git add` (only what the user wants in this PR).
4. Commit with the message: `git commit -m "<message>"`
5. Push the current branch: `git push -u origin <current-branch>` (or `git push` if upstream is set).
6. Open a pull request:
   - If `gh` (GitHub CLI) is available: `gh pr create --title "<title>" --body "<description>"`
   - Otherwise, output the repository URL and branch so the user can open the PR in the browser.
7. Return the PR URL or link when done.

## Notes

- Do not push or create the PR without explicit user approval (per project rules).
- Prefer a short title and a body that summarizes what changed and why.
- If there are merge conflicts or push is rejected, report and stop; do not force-push unless the user asks.
