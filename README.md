charstats
=========

**Dynamic [D&D Beyond](https://dndbeyond.com) character health overlay for streaming**

The D&D Beyond API doesn’t allow cross-origin requests, so we have to deal with that on the server side (tried using a [CORS Anywhere live demo](https://cors-anywhere.herokuapp.com) but it [429’s](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429) quite early). That’s what [fetch.php](fetch.php) is for— I kept it as generic as possible, so you can use whatever you want.

Massive thanks:
* [Roll20 API Scripts](https://github.com/RobinKuiper/Roll20APIScripts), for figuring out how to get a character’s natural max HP.
* [This Stack Overflow answer](https://stackoverflow.com/a/41135574), for providing the best way of mirroring response headers with PHP.
