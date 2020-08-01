jQuery(document).ready(function($){
    console.log('test');
    $(document).on('click', 'a[href!=""]:not([href^="#"],[href^="javascript:"])', function(event){
        event.preventDefault();
        alert('Outgoing links are disabled in iframe mode!');
    });
});