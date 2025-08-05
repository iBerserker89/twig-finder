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
