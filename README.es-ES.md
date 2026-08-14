

# php-curl
PHP utiliza cURL para implementar el inicio de sesión simulado y la recolección de datos, proporcionando muchos ejemplos. (Palabras clave: php curl login) 

Para conocer el uso de PHP cURL, consulte: 《[PHP cURL实现模拟登录与采集使用方法详解](http://www.zjmainstay.cn/php-curl)》

Generación rápida de código PHP basada en comandos cURL del navegador: [基于cURL命令解析获得PHP代码](http://demo.zjmainstay.cn/php/github/php-curl/demo/parseCurlGetPhpCode.php)

Ejemplo de inicio de sesión simulado automático + solicitudes por lotes: [模拟登录后批量下载笔记](https://github.com/Zjmainstay/php-curl/blob/master/demo/62_curlmulti/62_wiz.cn.php)

## Análisis y guía de uso del proyecto
Haga clic en el botón para ver
[![zread](https://img.shields.io/badge/Ask_Zread-_.svg?style=flat&color=00b0aa&labelColor=000000&logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQuOTYxNTYgMS42MDAxSDIuMjQxNTZDMS44ODgxIDEuNjAwMSAxLjYwMTU2IDEuODg2NjQgMS42MDE1NiAyLjI0MDFWNC45NjAxQzEuNjAxNTYgNS4zMTM1NiAxLjg4ODEgNS42MDAxIDIuMjQxNTYgNS42MDAxSDQuOTYxNTZDNS4zMTUwMiA1LjYwMDEgNS42MDE1NiA1LjMxMzU2IDUuNjAxNTYgNC45NjAxVjIuMjQwMUM1LjYwMTU2IDEuODg2NjQgNS4zMTUwMiAxLjYwMDEgNC45NjE1NiAxLjYwMDFaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00Ljk2MTU2IDEwLjM5OTlIMi4yNDE1NkMxLjg4ODEgMTAuMzk5OSAxLjYwMTU2IDEwLjY4NjQgMS42MDE1NiAxMS4wMzk5VjEzLjc1OTlDMS42MDE1NiAxNC4xMTM0IDEuODg4MSAxNC4zOTk5IDIuMjQxNTYgMTQuMzk5OUg0Ljk2MTU2QzUuMzE1MDIgMTQuMzk5OSA1LjYwMTU2IDE0LjExMzQgNS42MDE1NiAxMy43NTk5VjExLjAzOTlDNS42MDE1NiAxMC42ODY0IDUuMzE1MDIgMTAuMzk5OSA0Ljk2MTU2IDEwLjM5OTlaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik0xMy43NTg0IDEuNjAwMUgxMS4wMzg0QzEwLjY4NSAxLjYwMDEgMTAuMzk4NCAxLjg4NjY0IDEwLjM5ODQgMi4yNDAxVjQuOTYwMUMxMC4zOTg0IDUuMzEzNTYgMTAuNjg1IDUuNjAwMSAxMS4wMzg0IDUuNjAwMUgxMy43NTg0QzE0LjExMTkgNS42MDAxIDE0LjM5ODQgNS4zMTM1NiAxNC4zOTg0IDQuOTYwMVYyLjI0MDFDMTQuMzk4NCAxLjg4NjY0IDE0LjExMTkgMS42MDAxIDEzLjc1ODQgMS42MDAxWiIgZmlsbD0iI2ZmZiIvPgo8cGF0aCBkPSJNNCAxMkwxMiA0TDQgMTJaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00IDEyTDEyIDQiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L3N2Zz4K&logoColor=ffffff)](https://zread.ai/Zjmainstay/php-curl)

## Dependencias

PHP: >=5.4

## Uso individual

```php
git clone https://github.com/Zjmainstay/php-curl.git
cd php-curl
composer install
# Prueba
php demo/1_simpleAutoLogin.php
```

## Inclusión con Composer

```php
composer require zjmainstay/php-curl

# Prueba
cd vendor/zjmainstay/php-curl
composer install
php demo/1_simpleAutoLogin.php
```

## Inclusión directa de la clase

```php
require_once __DIR__.'/../src/CurlAutoLogin.php'; # Nota la ruta al usar
$autologin = new PHPCurl\CurlAutoLogin();

# Consulte demo/0_simpleAutoLogin.php
```

## Introducción a CurlAutoLogin

### Propiedades

```php
// Archivo de almacenamiento de cookies más reciente
protected $lastCookieFile = '';
// Bloquea la actualización de cookies después de un inicio de sesión exitoso
protected $lockedLastCookieFile = false;
// Ruta del registro
protected $logPath = '';
// Opciones globales, para facilitar el uso de proxies, etc.
protected $globalOpts = [];
// Parámetros de la última solicitud, para repetir la solicitud
protected $lastExecParams = [];
```

### Métodos

```php
// ================== 1. Métodos comunes ==================
// Ejecuta y analiza el resultado según la información de curl, uno de los métodos principales y de uso común
public function execCurl($curlContent, $callbackBefore = false, $callbackAfter = false, $storeParams = true)

// Ejecuta el comando curl con cookies, uno de los métodos principales y de uso común, utiliza directamente los parámetros de cookie del encabezado del comando curl
public function execCurlWithCookie($curlContent, $callbackBefore = false, $callbackAfter = false)

// ================== 2. Métodos públicos comunes ==================
// Establece opciones globales de solicitud (para facilitar el uso de proxies, etc.)
public function setGlobalOpts($opts = [])

// Repite la solicitud; en escenarios que dependen del inicio de sesión, si falla, después de volver a iniciar sesión, llame a este método para reintentar la última solicitud
public function repeatRequest()

// Obtiene los parámetros de la última solicitud, para repetir la solicitud
public function getLastExecParams()

// Destruye activamente los parámetros de la última solicitud
public function unsetLastExecParams()

// Analiza la información de curl, el resultado devuelto incluye url/header/post/opt/cookie, post son los datos enviados mediante POST, opt es una extensión de respaldo de curl
public function parseCurl($curlContent)

// Establece la ruta del registro
public function setLogPath($logPath) 

// Obtiene el archivo donde se almacenaron las cookies la última vez
public function getLastCookieFile()

// Obtiene el contenido de las cookies almacenadas la última vez
public function getLastCookieContent()

// Agrega manualmente el contenido de las cookies al archivo de cookies almacenado la última vez
public function appendCookieContent($content)

// Establece el archivo donde se almacenarán las cookies la última vez
public function setLastCookieFile($cookieFile)

// Limpia las cookies almacenadas la última vez
public function removeLastCookie()

// Después del inicio de sesión exitoso, bloquea el archivo donde se almacenaron las cookies la última vez, para evitar sobrescrituras
public function lockLastCookieFile()

// Desbloquea el archivo donde se almacenaron las cookies la última vez
public function unlockLastCookieFile()

// Después del inicio de sesión exitoso, bloquea las cookies, permite obtener información de URL mediante GET
public function getUrl($url, $header = false)

// Después del inicio de sesión exitoso, bloquea las cookies, permite obtener información de URL mediante POST
public function postUrl($url, $postData = false, $header = false)

// Aserción que verifica si el contenido contiene un carácter específico (para verificar la información de inicio de sesión, como la palabra "Cerrar sesión")
public function assertContainStr($content, $substr)

// Obtiene un carácter de salto de línea, utilizado para mostrar información con saltos de línea
public function getLineBreak()

// Formatea las cookies del encabezado para el formato de almacenamiento en archivo
public function formatHeaderCookieToFileContent($headerCookie, $domain)

// Analiza el contenido de las cookies desde el contenido de curl y lo agrega para guardarlo en el archivo de cookies
public function addCookieFromCurl($curlContent, $domain)

// ================== 3. Métodos de bajo nivel ==================
// Ejecuta la solicitud curl, método central de bajo nivel, que implementa el almacenamiento de cookies y el seguimiento de las cookies de la última solicitud, para lograr la dependencia de cookies en el inicio de sesión simulado
protected function _execCurl($parseCurlResult)

// Registra el registro, método de bajo nivel, que registra el registro cuando ocurre una excepción
protected function _log($msg)
```

## Introducción a los demos

```php
// Ejemplo simple de inicio de sesión simulado, llama directamente a la clase CurlAutoLogin
0_simpleAutoLogin.php

// Ejemplo simple de inicio de sesión simulado, que incluye la clase CurlAutoLogin mediante Composer
// Descripción: Incluye el callback execCurl $callbackBefore para reemplazar el nombre de usuario antes de la solicitud curl
1_simpleAutoLogin.php

// Inicio de sesión en WeChat desde el navegador, utilizando cookies después del inicio de sesión para accesos posteriores
// Descripción: 【El código ya no funciona, consulte la idea】
// Método uno: Utiliza el callback previo a execCurl para agregar cookies al encabezado
// Método dos: Llama directamente a parseCurl para analizar el comando curl, y ensambla un encabezado que contiene cookies para la solicitud
// Método tres: Llama directamente a execCurlWithCookie
2_wxqq.php
  
// Precarga cookies, luego accede a otras páginas
// Descripción: A veces el inicio de sesión simulado depende de las cookies de la página principal u otras páginas, por lo que es necesario llamar a execCurl para realizar una solicitud primero, almacenar las cookies de esa página y luego realizar las solicitudes posteriores
3_preloadCookieAndVisit.php

// Descarga de CNKI (China National Knowledge Infrastructure)
// Descripción: Agrega el atributo CURLOPT_HEADER para devolver información del encabezado de la solicitud, luego analiza la URL de descarga para realizar la solicitud de descarga
7_cnki.net.php

// Envío de mensajes en WebQQ
// Descripción: 【La función ya no está disponible】 Envío de mensajes grupales y personales basado en Web2 QQ (Nota: El envío frecuente bloqueará la cuenta, ¡úselo con precaución!)
8_webqq.php

// Inicio de sesión simulado en cnblogs
// Descripción: 【La página web cambió su diseño, la función ya no funciona】
// Basado en Node.js para proporcionar procesamiento de encriptación JS, devuelve el resultado para que PHP lo use en la solicitud de inicio de sesión
11_cnblogLogin

// (Verificación por código) Inicio de sesión en zuoche
// Descripción: La página web de zuoche muestra un código de verificación, el usuario lo ingresa manualmente y lo envía al programa para lograr el inicio de sesión simulado
12_zuocheWithCaptcha.php

// Inicio de sesión simulado en 360doc
// Descripción: Inicio de sesión en 360doc, hay una capa intermedia de dependencia de cookies, similar al demo 3_preloadCookieAndVisit.php
14_360doc.php

// Descarga de contenido de WizNote
// Descripción: Utiliza JS en la consola para ejecutar y obtener todos los enlaces de colección de notas, luego descarga las notas correspondientes
15_downloadWizNote.php

// Método genérico de recolección de páginas
// Descripción: Útil para solicitudes curl de uso diario
16_commonCurl/curlPage.php

// (Verificación por código) Ingreso de código de verificación desde la línea de comandos para iniciar sesión en dev.360.cn
// Descripción: La línea de comandos ejecuta para obtener el código de verificación, lo escribe en un archivo, la línea de comandos espera la entrada del código, luego lo envía al programa para lograr el inicio de sesión simulado
17_dev.360.cn_STDINCaptcha.php

// Búsqueda por palabras clave en Tianyancha
// Descripción: 【La función ya no está disponible】 Búsqueda por palabras clave en Tianyancha, la página de datos final tiene una dependencia de token, la extrae y pasa mediante $callbackBefore
19_www.tianyancha.com.php

// (Verificación por código) Inicio de sesión de usuario en Mumayi
// Descripción: Inicio de sesión de usuario en Mumayi, la página web muestra un código de verificación, el usuario lo ingresa manualmente y lo envía al programa para lograr el inicio de sesión simulado
20_u.mumayi.com_OnlineCaptcha.php

// Recolección de datos de cotizaciones en tiempo real en Global Forex
// Descripción: 【La página web cambió su diseño, ya no se requiere algo tan complejo】
// Carga la página, analiza el JS, llama a PhantomJS para ejecutar JS y obtener el resultado encriptado de las cookies, lo entrega al programa PHP para que lo llame
22_tools.cnforex.com-phantomjs-parse-js.php

// (Verificación por código) Inicio de sesión en Haodanku
// Descripción: La línea de comandos ejecuta para obtener el código de verificación, lo escribe en un archivo, la línea de comandos espera la entrada del código, luego lo envía al programa para lograr el inicio de sesión simulado
26_publish.haodanku.com.php
  
// Desencriptación JS de Ali Literature
// Descripción: Análisis de novelas en Ali Literature, basado en PhantomJS para ejecutar JS y realizar el análisis de contenido
34_www.aliwx.com.cn

// Subida de imágenes a un servicio de alojamiento de imágenes
// Descripción: 【La función del sitio 502 ya no está disponible】 Accede a la página inicial para obtener x-csrf-token, lo analiza y lo pasa mediante $callbackBefore para lograr la solicitud simulada de subir imágenes al sitio web de alojamiento de imágenes
38_upload.otar.im_image_upload.php

// Análisis de QR para subida de imágenes a CLI.im
// Descripción: Sube la imagen a CLI.im, luego utiliza el resultado de la subida para llamar a la interfaz de análisis y obtener el resultado
41_cli.im.php

// Repetición de solicitud
// Descripción: Primero simula una solicitud sin estado de inicio de sesión, después de una excepción simula el inicio de sesión, luego repite la solicitud para obtener un resultado normal
48_request_repeat.php

// Demo de extracción de tophub.today
// Descripción: Extracción del sitio web de tendencias de Zhihu
61_tophub.today.php
  
// 【Recomendación destacada】 Inicio de sesión simulado en WizNote, descarga masiva de notas
// Descripción: Este ejemplo combina el inicio de sesión simulado con la biblioteca de ejecución por lotes ares333/php-curl, demostrando cómo, después del inicio de sesión simulado, descargar notas por lotes basándose en el estado de inicio de sesión
62_curlmulti/62_wiz.cn.php

// Código PHP obtenido mediante análisis de curl
parseCurlGetPhpCode.php

```
