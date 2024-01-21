# Contributing to Nexus

Welcome to the Nexus Community!

We're thrilled you're considering contributing to Nexus! This document provides guidelines and advice 
for contributing to our project. Nexus is committed to fostering an open, welcoming, and inclusive 
environment. We value all contributions, whether it's coding, documentation, design, or spreading the word.

## How to Contribute

### Be Kind & Collaborative

We're all here to make the project better. Please be kind and respectful to your fellow contributors.

### Claim or Create an Issue

If you wish to help close an existing issue, please comment on that issue to let us know you're working on it.
This will avoid duplicated efforts from contributors on the same issue, and give us a chance to provide feedback
and guidance on your approach.

If you have an idea for a new feature or improvement, please create an issue to discuss it before you start work
on it. We can't promise to accept every PR that comes our way, and we'd like to give your work the best possible
chance to make it into main. 

### Ensure Style Consistency

We provide both a pint.json configuration and a git pre-commit hook (see setup.sh) to help you ensure that your
code is consistent with the rest of the project. Please apply this style to your code before submitting a PR.

This will prevent us from needing to review changes with unnecessary style-change noise. 

### Squash Commits

Your commits should be squashed. This will allow us to merge, revert, or cherry-pick your changes more easily. 

### Write Tests

All new features and bug fixes should include tests. We use Pest for testing. 

All existing tests must pass before we can merge your PR.
