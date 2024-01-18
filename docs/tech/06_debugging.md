[BumbleDocGen](/docs/README.md) **/**
[Technical description of the project](/docs/tech/readme.md) **/**
Debug documents

---


# Debug documents

Our tool provides several options for debugging documentation.

1) Firstly, after each generation of documents, you can make sure that the linking of documents was normal and no problems arose: after completing the documentation generation process, we display a list of all errors that occurred in the console:

    **Here is an example of error output:**

    <img src="/docs/assets/error_example.png?raw=true">

2) To track exactly how documentation is generated, you can use the interactive mode:

   `vendor/bin/bumbleDocGen serve` - So that the generated documentation changes automatically with changes in templates

   **or**

   `vendor/bin/bumbleDocGen serve --as-html` - So that the generated documentation changes automatically with changes in templates and is displayed as HTML on the local development server
3) Logs are saved to a special file `last_run.log` which is located in the working directory


---

**Last page committer:** fshcherbanich &lt;filipp.shcherbanich@team.bumble.com&gt;<br>**Last modified date:**   Thu Jan 18 14:38:29 2024 +0300<br>**Page content update date:** Thu Jan 18 2024<br>Made with [Bumble Documentation Generator](https://github.com/bumble-tech/bumble-doc-gen/blob/master/docs/README.md)