!function(o){o(function(){function e(){var e=function(){},i=function(){var e,i,t=[[14122944e5,0],[14123808e5,0],[14124672e5,0],[14125536e5,0],[141264e7,0],[14127264e5,0],[14128128e5,0],[14128992e5,0]],l=[[14122944e5,0],[14123808e5,0],[14124672e5,0],[14125536e5,0],[141264e7,0],[14127264e5,0],[14128128e5,0],[14128992e5,0]],r="7days",n=[[1,13],[2,16],[3,14],[4,12],[5,17],[6,15],[7,12]],s=[[1,13],[2,29],[3,43],[4,55],[5,72],[6,87],[7,99]],a=function(o){for(var e=0,i=0,t=o.length;t>e;e++)i+=o[e][1];return i},h=a(t),d=h/t.length,c=a(l),b=c/l.length;o("#lp_js_avg-items-sold").html(d.toFixed(1)),o("#lp_js_total-items-sold").html(h),o("#lp_js_total-revenue").html(c.toFixed(2)),o("#lp_js_avg-revenue").html(b.toFixed(2)),"7days"===r?(e="%a",i="time"):"30days"===r?(e="%m/%d",i="time"):i=null,o.plot(o("#lp_js_graph-conversion"),[{data:[[1,100],[2,100],[3,100],[4,100],[5,100],[6,100],[7,100]],bars:{show:!0,barWidth:.7,fillColor:"#e3e3e3",lineWidth:0,align:"center",horizontal:!1}},{data:n,bars:{show:!0,barWidth:.35,fillColor:"#52CB75",lineWidth:0,align:"center",horizontal:!1}}],{legend:{show:!1},xaxis:{font:{color:"#bbb",lineHeight:18},show:!0,ticks:[[1,"Mon"],[2,"Tue"],[3,"Wed"],[4,"Thu"],[5,"Fri"],[6,"Sat"],[7,"Sun"]]},yaxis:{font:{color:"#bbb"},ticks:5,tickFormatter:function(o){return o+" %"},min:0,max:100,reserveSpace:!0},series:{shadowSize:0},grid:{borderWidth:{top:0,right:0,bottom:1,left:0},borderColor:"#ccc",tickColor:"rgba(247,247,247,0)"}}),o.plot(o("#lp_js_graph-units"),[{data:t,color:"#52CB75",lines:{show:!0,lineWidth:1.5,fill:!1,gaps:!0},points:{show:!0,radius:3,lineWidth:0,fill:!0,fillColor:"#52CB75"}},{data:s,color:"#52CB75",lines:{show:!0,lineWidth:1.5,fill:!1,gaps:!0},points:{show:!0,radius:3,lineWidth:0,fill:!0,fillColor:"#52CB75"}}],{legend:{show:!1},xaxis:{font:{color:"#bbb",lineHeight:18},mode:i,timeformat:e,show:!0},yaxis:{font:{color:"#bbb"},ticks:5,min:0,reserveSpace:!0},series:{shadowSize:0},grid:{borderWidth:{top:0,right:0,bottom:1,left:0},borderColor:"#ccc",tickColor:"rgba(247,247,247,0)"}}),o.plot(o("#lp_js_graph-revenue"),[{data:l,color:"#52CB75",lines:{show:!0,lineWidth:1.5,fill:!1,gaps:!0},points:{show:!0,radius:3,lineWidth:0,fill:!0,fillColor:"#52CB75"}}],{legend:{show:!1},xaxis:{font:{color:"#bbb",lineHeight:18},show:!0,mode:i,timeformat:e},yaxis:{font:{color:"#bbb"},ticks:5,min:0,reserveSpace:!0},series:{shadowSize:0},grid:{borderWidth:{top:0,right:0,bottom:1,left:0},borderColor:"#ccc",tickColor:"rgba(247,247,247,0)"}}),o(".lp_sparkline-bar").peity("bar",{width:34,height:14,gap:1,fill:function(){return"#ccc"}})},t=function(){e(),i()};t()}e()})}(jQuery);