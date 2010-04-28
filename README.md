# Yamte - Yet Another Minimalistic Template Engine

## About

Yamte is a simple template engine built in PHP, based on VTemplate. Whereas the code syntax has been kept in its majority, the engine has been fully rebuilt with functionalities provided by PHP5.3 (which is required, naturally !).

## Download instructions

    git clone https://khazou@github.com/khazou/yamte.git

## HTML syntax

### Defining variables in the code

    <h1>{#var_title}</h1>
    <p>{#var_content}</p>

### Adding contexts

    <table>
    <!--YAT_my_context-->
      <tr><td>{#first_table_data}</td><td>{#second_table_data}</td></tr>
    <!--/YAT_my_context-->
    </table>

## PHP syntax

### Adding the template to the page and instanciate the class :

    <?php
    require_once 'path/to/Yamte.php';
    try {
      $tpl = new yamte\Yamte('path/to/template.tpl');

### Setting variables value in the php side

    $tpl->setVariable('var_title', 'Hello world');
    $tpl->setVariable('var_content', "This is my content. Nice, isn't it ?");

### Using contexts in the php code (For example in a for loop)

    for($i = 0; $i < 5; $i++) {
      $tpl->openContext('my_context');
      $tpl->setVariable('my_context.first_table_data', $i);
      $tpl->setVariable('my_context.second_table_data', $i * 2);
      $tpl->closeContext('my_context');
    }

### Ending the try-catch data

    } catch (yamte\YAException $e) {
      echo $e->getTraceData();
    }

## Licence

Yamte is released under the GPLv3 Licence. You can find the official licence text on [http://www.gnu.org/licenses/gpl.html](http://www.gnu.org/licenses/gpl.html "GPLv3 Licence").
