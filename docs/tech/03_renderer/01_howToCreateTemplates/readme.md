[BumbleDocGen](/docs/README.md) **/**
[Technical description of the project](/docs/tech/readme.md) **/**
[Renderer](/docs/tech/03_renderer/readme.md) **/**
How to create documentation templates?

---


# How to create documentation templates?

Templates are `twig` files in which you can write both static text and dynamic blocks that will change from code changes or other required parameters.

**You can read more about template parts here:**


- [Front Matter](/docs/tech/03_renderer/01_howToCreateTemplates/frontMatter.md)
- [Templates dynamic blocks](/docs/tech/03_renderer/01_howToCreateTemplates/templatesDynamicBlocks.md)
- [Linking templates](/docs/tech/03_renderer/01_howToCreateTemplates/templatesLinking.md)
- [Templates variables](/docs/tech/03_renderer/01_howToCreateTemplates/templatesVariables.md)

## Examples

### 1) An example of a template with fully static text:

```twig
Some static text
This text does not change when the code is changed
```

After generating the documentation, this page will look exactly like a template.

### 2) An example of a template with static text and dynamic blocks:

```twig
---
title: Some page
prevPage: Technical description of the project
---
{{ generatePageBreadcrumbs(title, _self) }}

Some static text...

Dynamic block:

{{ printEntityCollectionAsList(phpEntities.filterByInterfaces(['\\BumbleDocGen\\Core\\Parser\\SourceLocator\\SourceLocatorInterface']).getOnlyInstantiable()) }}

More static text...
```

Result after starting the documentation generation process:

```md
<embed> <a href="/docs/readme.md">BumbleDocGen</a> <b>/</b> <a href="/docs/tech/index.md">Technical description of the project</a> <b>/</b> Some page<hr> </embed>

Some static text...

Dynamic block:

<embed> <ul><li><a href=\'/docs/tech/3.renderer/classes/DirectoriesSourceLocator.md\'>DirectoriesSourceLocator</a> - Loads all files from the specified directory</li><li><a href=\'/docs/tech/3.renderer/classes/FileIteratorSourceLocator.md\'>FileIteratorSourceLocator</a> - Loads all files using an iterator</li><li><a href=\'/docs/tech/3.renderer/classes/RecursiveDirectoriesSourceLocator.md\'>RecursiveDirectoriesSourceLocator</a> - Loads all files from the specified directories, which are traversed recursively</li><li><a href=\'/docs/tech/3.renderer/classes/SingleFileSourceLocator.md\'>SingleFileSourceLocator</a> - Loads one specific file by its path</li><li><a href=\'/docs/tech/3.renderer/classes/AsyncSourceLocator.md\'>AsyncSourceLocator</a> - Lazy loading classes. Cannot be used for initial parsing of files, only for getting specific documents</li></ul> </embed>

More static text...

<div id=\'page_committer_info\'>
<hr>
<b>Last page committer:</b> fshcherbanich &lt;filipp.shcherbanich@team.bumble.com&gt;<br><b>Last modified date:</b>   Sat Jul 29 17:43:49 2023 +0300<br><b>Page content update date:</b> Sun Jul 30 2023<br>Made with <a href=\'/docs/readme.md\'>Bumble Documentation Generator</div>
```

This is how it looks on the GitHub:

<img src="/docs/assets/doc_example.png?raw=true">


### 3) Another example of a dynamic block:

Output method description as a dynamic block:

```twig
Some static text...

Dynamic block:

{{ phpEntities
    .get('\\BumbleDocGen\\LanguageHandler\\LanguageHandlerInterface')
    .getMethod('getLanguageKey')
    .getDescription()
}}

More static text...
```

Result after starting the documentation generation process:

```twig
Some static text...

Dynamic block:

Unique language handler key

More static text...
```

---

**Last page committer:** fshcherbanich &lt;filipp.shcherbanich@team.bumble.com&gt;<br>**Last modified date:**   Thu Jan 18 14:38:29 2024 +0300<br>**Page content update date:** Thu Jan 18 2024<br>Made with [Bumble Documentation Generator](https://github.com/bumble-tech/bumble-doc-gen/blob/master/docs/README.md)