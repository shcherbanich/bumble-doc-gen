[BumbleDocGen](/docs/README.md) **/**
[Technical description of the project](/docs/tech/readme.md) **/**
Renderer

---


# Documentation renderer

Render passes through all files from the directory specified in configuration param `templates_dir`

If the file ends with **.twig** then the file is processed, otherwise it is simply copied
to the target directory obtained from configuration param `output_dir`.
We use twig to process templates.

## More detailed description of renderer components


- [How to create documentation templates?](/docs/tech/03_renderer/01_howToCreateTemplates/readme.md)    
    - [Front Matter](/docs/tech/03_renderer/01_howToCreateTemplates/frontMatter.md)
    - [Templates dynamic blocks](/docs/tech/03_renderer/01_howToCreateTemplates/templatesDynamicBlocks.md)
    - [Linking templates](/docs/tech/03_renderer/01_howToCreateTemplates/templatesLinking.md)
    - [Templates variables](/docs/tech/03_renderer/01_howToCreateTemplates/templatesVariables.md)
- [Documentation structure and breadcrumbs](/docs/tech/03_renderer/02_breadcrumbs.md)
- [Document structure of generated entities](/docs/tech/03_renderer/03_documentStructure.md)
- [Template filters](/docs/tech/03_renderer/04_twigCustomFilters.md)
- [Template functions](/docs/tech/03_renderer/05_twigCustomFunctions.md)

## Starting the rendering process

```php
$renderer = new Renderer(...);

// Starting the process of filling templates with data and saving finished documents
$renderer->run();
```

## How it works

The process of rendering documents is divided into several stages. We separately generate documentation for templates that were pre-prepared by the user,
and then create documentation for classes that the user refers to from document templates.
This process is presented in the form of a diagram below.

```mermaid
 flowchart TB
  Start((Start)) --> InitRender(Initialization of Renderer)
  InitRender --> StartRender(Start rendering process)
  StartRender --> EnterLoop(Enter file processing loop from <b>templates_dir</b> directory)
  subgraph TemplatesProcessing[Documentation templates processing]
    EnterLoop --> NextFileExists{Have \nthe next \nfile to \nprocess?}
    NextFileExists -- Yes --> CheckTwig{Is it a twig \ntemplate?}
    CheckTwig -- No --> SaveAndNext(Save to <b>output_dir</b>)
    CheckTwig -- Yes --> TemplateProcessing(Process template. Fill with content.)
    SaveAndNext --> NextFileExists
    TemplateProcessing --> CheckEntityLink{Does the \ntemplate have \nentity links?}
    CheckEntityLink -- Yes --> AddToList(Add an entity to the list for documentation)
    CheckEntityLink -- No --> SaveAndNext
    AddToList --> SaveAndNext
  end

  subgraph EntityProcessing[Processing entities from \nthe list for documentation]
    NextFileExists -- No --> StartEntityProcessing
    StartEntityProcessing(Start processing \nentities from \ndocumentation list) --> FileToProcess{Have \nthe next \nentity to \nprocess?}
    FileToProcess -- Yes --> SaveEntityDoc
    SaveEntityDoc[Save entity documentation] --> FileToProcess
  end

  FileToProcess -- No --> Exit(((Completing the \nrendering process)))

  style TemplatesProcessing stroke:#f66,stroke-width:2px,color:#fff,stroke-dasharray: 5 5
  style EntityProcessing stroke:#f66,stroke-width:2px,color:#fff,stroke-dasharray: 5 5
```

---

**Last page committer:** fshcherbanich &lt;filipp.shcherbanich@team.bumble.com&gt;<br>**Last modified date:**   Thu Jan 18 14:38:29 2024 +0300<br>**Page content update date:** Thu Jan 18 2024<br>Made with [Bumble Documentation Generator](https://github.com/bumble-tech/bumble-doc-gen/blob/master/docs/README.md)