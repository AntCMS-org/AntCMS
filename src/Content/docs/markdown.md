--AntCMS--
Title: Markdown support
Author: The AntCMS Team
Description: Learn about what Markdown syntax and features AntCMS supports.
--AntCMS--

## Markdown

AntCMS utilizes markdown to streamline the process of creating website content, eliminating the need for advanced and complex content editors. This allows for an efficient writing experience, where users can write without interruption, as styling can be added directly to the content as it is being written.

Not sure what markdown is? Here's a [cheat sheet](https://www.markdownguide.org/cheat-sheet/) on it's syntax, that same website also offers more insight into what markdown is and detailed guides for writing in markdown. Once you learn the markdown syntax, it's very easy to quickly write content with it.

AntCMS supports the full basic markdown syntax, plus added support for the GitHub Flavored Markdown (GFM) and some additional options. Here is a code example of all syntax AntCMS supports with the rendered example below:

```markdown
## Basic Syntax
## Headers!

**Bold text**, *italicized text*, ~~striked out text~~

> block quotes

1. Ordered lists
2. Hey look, a second item!

- Unordered lists
- Hey, it's another item!

`code blocks`

Horizontal rules:

---

Links: [title](https://www.example.com)

Pictures:
![alt text](https://picsum.photos/seed/picsum/128/128)

## Extended Syntax

~~Strikethrough.~~

Tables:

| Syntax | Description |
| ----------- | ----------- |
| Header | Title |
| Paragraph | Text | 

Fenced code blocks:
Note: we can't put the codeblock here inside of a codeblock, so you'll just have to trust us :wink:

Task lists:
- [x] This one is done
- [ ] But, this one isn't

Emoji support! :joy:

And finally.. emeded content, by putting a URL on a line of it's own, the content will automatically be embeded in the page.
Note: support depends on what site the content is on, but AntCMS will always fallback to a regular link

https://www.youtube.com/watch?v=dQw4w9WgXcQ

```

And now the same content, but rendered through AntCMS:

## Basic Syntax
## Headers!

**Bold text**, *italicized text*, ~~striked out text~~

> block quotes

1. Ordered lists
2. Hey look, a second item!

- Unordered lists
- Hey, it's another item!

`code blocks`

Horizontal rules:

---

Links: [title](https://www.example.com)

Pictures:
![alt text](https://picsum.photos/seed/picsum/128/128)

## Extended Syntax

~~Strikethrough.~~

Tables:

| Syntax | Description |
| ----------- | ----------- |
| Header | Title |
| Paragraph | Text | 



Task lists:
- [x] This one is done
- [ ] But, this one isn't

Emoji support! :joy:

And finally.. embedded content, by putting a URL on a line of it's own, the content will automatically be embeded in the page.
Note: support depends on what site the content is on, but AntCMS will always fallback to a regular link

https://www.youtube.com/watch?v=dQw4w9WgXcQ
