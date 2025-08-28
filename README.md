# Twig Finder

> CLI para localizar rapidamente arquivos `.html.twig` em projetos Drupal com sugestões inteligentes baseadas em `theme hook suggestions`.

## Versão

**v0.1.0**

## Requisitos

- PHP 8.0+
- Composer
- Estrutura de projeto Drupal

## Instalação

Clone o repositório e instale as dependências com o Composer:

```bash
composer install
```

Torne o comando acessível globalmente (opcional):

```bash
sudo ln -s $(pwd)/twig-finder.php /usr/local/bin/twig-finder
```

## Uso

```bash
php twig-finder.php search <nome-do-template> [caminho]
```

Exemplos:

```bash
php twig-finder.php search user_register_form
php twig-finder.php search node__articles ~/meu-projeto
twig-finder search block__footer --exact
```

## Sugestões Inteligentes

Caso nenhum arquivo seja encontrado, o sistema sugere nomes de arquivos com base na saída do `theme debug` do Drupal.

Exemplo:

```
🧠 form--user-register.html.twig
🧠 node--articles.html.twig
```

## Estrutura

- `twig-finder.php` — Arquivo principal que inicializa o console
- `src/Command/SearchCommand.php` — Implementação do comando `search`

## Contribuições

Sinta-se à vontade para abrir issues, sugerir melhorias ou enviar pull requests!

## MIT License

Copyright (c) 2025 Luciano Barros

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

