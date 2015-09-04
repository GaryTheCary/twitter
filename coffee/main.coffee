# Variables that will be used
# Try to define a class that store all the useable variabls inside
tempstorage = []
count = 0
comstorage = []

class g_var
  constructor: (@num, @target_array, @com_array)->
  addFeed: (username, time, textfield)->
    targetfield = document.getElementById "viewfeed"
    if targetfield? 
      # If the field is not none then create a new field 
      divider = document.createElement 'div'
      divider.className = "ui horizontal divider"
      div_text = document.createTextNode 'Next'
      divider.appendChild div_text

      event = document.createElement 'div'
      event.className = "event"
      label = document.createElement 'div'
      label.className = "label"
      img = document.createElement 'img'
      img.src = "/bower_components/semantic-ui/examples/assets/images/avatar/nan.jpg"

      label.appendChild img

      content = document.createElement 'div'
      content.className = "content"

      summary = document.createElement 'div'
      summary.className = "summary"
      user = document.createElement 'a'
      user.className = "user"
      user_text = document.createTextNode username
      user.appendChild user_text
      date = document.createElement 'div'
      date.className = "date"
      date.innerHTML = time  
      
      summary.appendChild user
      summary.appendChild date

      message = document.createElement 'div'
      message.className = "extra text"
      twits = document.createTextNode textfield
      message.appendChild twits 

      # wrap up

      content.appendChild summary
      content.appendChild message

      event.appendChild label
      event.appendChild content

      targetfield.appendChild divider
      targetfield.appendChild event    
  refreshingcheck: ->
    index = 0
    for i in @target_array
      @com_array[index] = i
      @addFeed i.name, i.date, i.text
      index++  
private_var = new g_var(count, tempstorage, comstorage)

postrequest =->
  data = "what"
  $.post "https://api.particle.io/v1/devices/events", {name: "receive_text_event", data: data, access_token: "974763a20335837fb135f10cead6e1d651870247"}, (data)->
    alert("Data sent: " + data)

$(document).ready ->
  stopBtn = document.getElementById("stop")
  startBtn = document.getElementById("start")
  startBtn.onclick =->
     data = $("#keywordfield").val()
     reg = /^#/
     if reg.test(data) != true
        data = "#" + data
     $.post "/server/gethashtag.php", {data: data}, (data)->
      alert("Data Loaded: " + data)
      document.getElementById("gethashtag").reset() 
  stopBtn.onclick =->
    postrequest()
  setInterval ->
    if private_var.num > 9
      private_var.num = 0
    if private_var.target_array.length == 0
      console.log "eh"
    else
      if private_var.com_array.length == 0
        private_var.refreshingcheck()
      else
        if private_var.com_array[0].text == private_var.target_array[0].text
          console.log "the same"
        else
          console.log  "no same"
          private_var.refreshingcheck()          
  , 1000      

# Testing area
if !!window.EventSource 
  source = new EventSource("/server/event.php")
  source.addEventListener 'message', (e)->
    msg = JSON.parse e.data
    private_var.target_array[private_var.num] = msg
    private_var.num++  
  , false

  





        





