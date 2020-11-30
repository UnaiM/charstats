<?php
foreach($_GET as $key=>$val) {
  if (!$val && preg_match('/^[\d,]+$/', $key)) {
    $ids = $key;
    break;
  }
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>DDB character link &ndash; <?php echo $ids ? 'Result' : 'Start' ?></title>
    <style>
      body {
        background-color: transparent !important;
        font-family: sans-serif;
        margin: 0;
        font-size: 1.1em;
      }
      textarea, input {
        background-color: inherit;
        color: inherit;
        font-size: inherit;
        margin: 0.2em 0 0.2em 1em;
        padding: 0.3em 0.4em 0.25em;
        border: 1px solid #60666f;
        border-radius: 0.3em;
      }
      textarea {
        margin-top: 1em;
        display: block;
        font-family: inherit;
      }
      textarea, input:active {
        background-color: rgba(0, 0, 0, 10%);
      }
      textarea:focus, textarea:hover, input:focus, input:hover {
        outline: none;
        border-color: #90969f;
      }
      #submit {
        margin-left: 0.1em;
      }
      table {
        border-collapse: collapse;
      }
      @keyframes beat {
        from {background-color: rgba(0, 128, 255, 20%);}
        to {background-color: inherit;}
      }
      .beat td:not(.barcell) {
        animation-name: beat;
        animation-duration: 1.5s;
      }
      td {
        padding: 0 1em;
        border-bottom: 1px solid #60666f;
        border-right: 1px solid #60666f;
        margin-bottom: -1px;
      }
      .barcell {
        display: block;
        position: relative;
        width: 500px;
        height: 70px;
        overflow: hidden;
        padding: 0;
      }
      .hpshadow {
        width: 40%;
        height: 20%;
      	border-radius: 9999em;
        background-color: rgba(60, 20, 0, 50%);
      	box-shadow: 0 0 0.2em rgba(60, 20, 0, 100%);
        position: absolute;
        left: 6%;
        top: 40%;
        margin: 0;
      }
      .hpavail {
        width: 100%;
        height: 100%;
      	border-radius: 9999em;
      	padding: 0.1em;
        margin: -0.2em;
        border: 0.1em solid transparent;
        transition: width 0.5s, box-shadow: 0.5s;
      }
      .hpmark {
        position: absolute;
        width: 2px;
        height: 250%;
        left: 100%;
        top: -75%;
        background-image: linear-gradient(to top, rgba(255, 255, 255, 0%) 0%, rgba(255, 255, 255, 100%) 30%, rgba(255, 255, 255, 0%) 50%, rgba(255, 255, 255, 100%) 70%, rgba(255, 255, 255, 0%) 100%);
        transition: opacity 0.5s;
      }
      .hpcurrent {
        position: absolute;
        top: 0;
        left: -1px;
        height: 100%;
        border-radius: 9999em;
        box-shadow: inset 0 0.1em 0.2em rgba(255, 255, 255, 40%), inset 0 -0.1em 0.2em rgba(0, 0, 0, 40%);
        transition: width 0.5s, background-color 0.5s;
        background-image: linear-gradient(to top, rgba(0, 0, 0, 50%) 0%, rgba(255, 255, 255, 20%) 100%);
        margin-right: -2px;
      }
      .hptemp {
        position: absolute;
        top: 12%;
        height: 70%;
        border-radius: 9999em;
        transition: width 0.5s, margin-left 0.5s;
        background-color: #eff;
        box-shadow: 0 0 0.5em #3df;
      }
      a {
        color: inherit;
      }
      a:focus {
        outline: none;
        position: relative;
      }
      a:hover {
        color: #A0A7B1;
      }
      .right, .slash, .parenth, .plus {
        text-align: right;
      }
      .right, .slash, .parenth {
        border-right: none;
        padding-right: 0.3em;
      }
      .slash, .parenth, .plus {
        position: relative;
        padding-left: 0.3em;
      }
      .slash::before, .parenth::before, .parenth::after, .plus::before {
        display: block;
        position: absolute;
      }
      .parenth:empty::before, .parenth:empty::after, .plus:empty::before {
        display: none;
      }
      .slash::before {
        content: '/';
        left: -0.1em;
      }
      .parenth::before {
        content: '(';
        left: 0;
      }
      .parenth {
        padding-right: 0.8em;
      }
      .parenth::after {
        content: ')';
        right: 0.45em;
        top: 50%;
        margin-top: -0.62em;
      }
      .plus::before {
        content: '+';
        left: -0.25em;
      }
    </style>
  </head>
  <body>
<?php if (!$ids): ?>
    <textarea id='urls' placeholder='Separate character sheet URLs with line breaks, e.g.:
  dndbeyond.com/profile/UserName/characters/1234567
  http://www.dndbeyond.com/characters/89012345
  https://ddb.ac/characters/876543/aBcDe' rows='8' cols='80' autocomplete='off' wrap='off' required autofocus></textarea>
    <input type='button' id='submit' value='Submit'>
    <script>
      const urls = document.getElementById('urls')
      document.getElementById('submit').addEventListener('click', function() {
        let ids = []
        urls.value.split('\n').forEach((line) => {
          let val = /(?<=\/characters\/|^)[\d]+/.exec(line)
          if (val) {
            ids.push(val)
          }
        })
        window.location.href = '?'+ids.join(',')
      })
    </script>
<?php else: ?>
    <table>
<?php foreach(explode(',', $ids) as $id) { ?>
      <tr id='c<?php echo $id; ?>'><td class='barcell'><div class='hpshadow'><div class='hpavail'></div><div class='hpcurrent'></div><div class='hptemp'></div><div class='hpmark'></div></td><td><a href='https://dndbeyond.com/characters/<?php echo $id; ?>' target='_blank'>Loading&hellip;</a></td><td class='right'></td><td class='slash'></td><td class='parenth'></td><td class='plus'></td></tr>
<?php } ?>
    </table>
    <script>
      [<?php echo $ids; ?>].forEach((id) => {
        const xhr = new XMLHttpRequest();
        xhr.responseType = 'json'
        xhr.timeout = 10000

        let send_ = true
        function send() {
          const inter = window.setInterval(function() {
            if (send_) {
              window.clearInterval(inter)
              send_ = false
              window.setTimeout(function() {
                send_ = true
              }, 2000)
              xhr.open('GET', 'worker.php?id='+id)
              xhr.send()
            }
          }, 200)
        }

        let current = null
        let max = null
        let natural = null
        let temp = null
        xhr.addEventListener('load', function() {
          const row = document.getElementById('c'+id)
          xhr.response.forEach((v, i) => {
            (i ? row.children[i+1] : row.children[1].children[0]).innerText = i==3 && v==xhr.response[2] || i==4 && v==0 ? '' : v
          })

          const hpshadow = row.getElementsByClassName('hpshadow')[0]
          const hpavail = row.getElementsByClassName('hpavail')[0]
          const hpmark = row.getElementsByClassName('hpmark')[0]
          const hpcurrent = row.getElementsByClassName('hpcurrent')[0]
          const hptemp = row.getElementsByClassName('hptemp')[0]
          let ch_col = [null, 0]
          for (const [col, score] of Object.entries({'#f00|#0f0': xhr.response[1] - current, '#000|#0ff': xhr.response[2] - max, 'rgba(255, 255, 255, 0.3)|#fea': xhr.response[3] - natural, '#f0c|#96f': xhr.response[4] - temp})) {
            if (score != 0) {
              const ascore = Math.abs(score)
              if (ascore > ch_col[1]) {
                ch_col = [col.split('|')[score>0 ? 1 : 0], ascore]
              }
            }
          }
          ch_col = ch_col[0]
          current = xhr.response[1]
          max = xhr.response[2]
          natural = xhr.response[3]
          temp = xhr.response[4]
          let val = max/natural
          hpavail.style.width = `${100*val}%`
          if (ch_col !== null) {
            hpavail.style.boxShadow = 'inset 0 0.1em 0.1em rgba(0, 0, 0, 40%), inset 0 -0.1em 0.1em rgba(255, 255, 255, 80%)' + (ch_col!==null ? `, 0 0 0.5em 0.3em ${ch_col}` : '')
            hpavail.offsetHeight;
            window.setTimeout(function() {
              hpavail.style.boxShadow = 'inset 0 0.1em 0.1em rgba(0, 0, 0, 40%), inset 0 -0.1em 0.1em rgba(255, 255, 255, 80%)'
              hpavail.offsetHeight;
            }, 200)
          }
          hpmark.style.opacity = val > 1 ? 1 : 0;
          const over = val > 1
          window.setTimeout(function() {
            const val = hpshadow.clientWidth + 3
            hpavail.style.backgroundImage = `linear-gradient(to top, rgba(255, 255, 255, 20%) 0%, rgba(0, 0, 0, 20%) 100%), linear-gradient(90deg, #899 ${val-2}px, #${over ? 'db6' : 899} ${val+2}px)`
          }, 500)
          val = current / natural
          hpcurrent.style.width = `${100*val}%`
          hpcurrent.style.backgroundColor = `hsl(${120*val}, 95%, 60%)`
          hptemp.style.marginLeft = `${100*val - (current ? 2 : 0)}%`
          hptemp.style.width = `${100*temp/natural}%`

          row.classList.remove('beat')
          row.offsetHeight // Forces a redraw.
          row.classList.add('beat')
          send()
        })
        xhr.addEventListener('timeout', xhr.send)
        send()
      })
    </script>
<?php endif; ?>
  </body>
</html>
