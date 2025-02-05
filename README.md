# Extra Simple Analytcis

Web page analytics for self-hosting, offering a private alternative to solutions like Google Analytics. It includes basic functionalities for collecting visitor data, along with a dashboard for reviewing and exporting data for further processing in other tools.


Code snippet for site integration 
```
<script type="text/javascript">
    //<![CDATA[
    var esa_baseUrl = '<esa-url>';
    var esa_setSiteId = '<esa-site-id>';
    
    (function() {
        var _esa = document.createElement('script'); _esa.type = 'text/javascript'; _esa.async = true;
        _esa.src = esa_baseUrl + 'esa.js';
        var _esa_s = document.getElementsByTagName('script')[0]; 
        _esa_s.parentNode.insertBefore(_esa, _esa_s);
    }());
    //]]>
</script>
```

Run composer script to download [bulma](https://bulma.io/) CSS 
```
composer run-script get-bulma
```

Run composer script to download [chart.js](https://www.chartjs.org/) 
```
composer run-script get-chartjs
```



## TODO:

- Filter results in time frame (7days, 2weeks, 1month, 1qurter, 1year?)

- Show last 20 visitors details 
- Show browsers summary
- Show locations summary 
- Show pages summary 
    - add gathering page title insted of just url/path

[x] Support multiple sites on deasboard
[x] Code snippet generator (site id)
[x] Validation of site if on beacon call

- Improve update process for geo ip data
- Keep user selected theme dark/light


