{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * Checko payment template
 *
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2015 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 *}

<script>
        var script = document.createElement('script');
        script.src = "https://cdn.pagamastarde.com/pmt-simulator/2/js/pmt-simulator.min.js";
        setTimeout(function(){
        document.body.appendChild(script);
        },2000);  // 2000 is the delay in milliseconds
</script>