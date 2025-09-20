
function chartInit(chartType) {

    fetch('/esa/api/' + chartType, {
    headers: {
        "Accept": "application/json",
      }
    })
        .then((response) => response.json())
        .then((json) => window['set' + chartType.charAt(0).toUpperCase() + chartType.slice(1) + 'Chart'](json));
}

function addDays(date, days) {
    var result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

function setVisitsChart(jsonData) {
    const ctx = document.getElementById('pages-status-chart');
    let labels = []
    let values = []

    for (item in jsonData['data']) {
        labels.push(item)
    }
    const minDate = labels[0].split('-')
    const maxDate = labels[(labels.length-1)].split('-')

    const dateMinObj = new Date(minDate[0], minDate[1]-1, minDate[2])
    const dateMaxObj = new Date(maxDate[0], maxDate[1]-1, maxDate[2])
    const elapsed = dateMaxObj - dateMinObj

    let minutes = Math.floor(elapsed / 60000)
    let hours = Math.round(minutes / 60)
    let days = Math.round(hours / 24) + 1

    labels = []
    stepDate = dateMinObj
    let isoDate
    

    for (let step = 0; step < days; step++) {
        labels.push((stepDate.getMonth()+1) + '/' + (stepDate.getDate()))
        isoDate = stepDate.toISOString().substring(0, 10)

        if (isoDate in jsonData['data']) {
            values.push(jsonData['data'][isoDate])
        } else {
            values.push(0)
        }

        stepDate = addDays(stepDate, 1)
    }
    const data = {
        labels: labels,
        datasets: [{
            label: 'Visits',
            data: values,
            fill: false,
            borderColor: baseColor,
            tension: 0.1
        }]
    };    
    const config = {
        type: 'line',
        data: data,
        options: {
            plugins: {
                legend: {
                    display: false,
                },
            },
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    min: 0,
                    ticks: {
                        stepSize: 2
                      }                    
                }
            }            
        }
      };

    new Chart(ctx, config)

}

function fillTable(data,name) {

    const template = document.getElementById('summaries-row');
    const table = document.getElementById(name + '-table');

    for (let key in data) {
        const clone = document.importNode(template.content, true);

        clone.querySelector('.summaries-name').textContent = key;
        clone.querySelector('.summaries-count').textContent = data[key];
        
        table.appendChild(clone)
    }
    
}

function setLocationsChart(jsonData) {
    const ctx = document.getElementById('locations-chart');

    fillTable(jsonData.data,'locations')
    const labels = Object.keys(jsonData.data)  
    const values = Object.values(jsonData.data)

    const colorsAraay = getColorsArray(labels.length)
    const data = {
        labels: labels,
        datasets: [{
            label: 'Locations',
            data: values,
            fill: false,
            backgroundColor: colorsAraay,
            borderColor: backgroundColor,
            tension: 0.1
        }]
    };    
    const config = {
        type: 'doughnut',
        data: data,
        options: {
            plugins: {
                legend: {
                    display: false,
                },
            },
            responsive: true,
            maintainAspectRatio: false         
        }
      };

    const chart = new Chart(ctx, config)    
    charts['location'] = chart
}

function setBrowsersChart(jsonData) {
    const ctx = document.getElementById('browsers-chart');

    fillTable(jsonData.data,'browsers')

    const labels = Object.keys(jsonData.data)  
    const values = Object.values(jsonData.data)
    const colorsAraay = getColorsArray(labels.length)
    const data = {
        labels: labels,
        datasets: [{
            label: 'Browsers',
            data: values,
            fill: false,
            backgroundColor: colorsAraay,
            borderColor: backgroundColor,
            tension: 0.1
        }]
    };    
    const config = {
        type: 'doughnut',
        data: data,
        options: {
            plugins: {
                legend: {
                    display: false,
                },
            },
            responsive: true,
            maintainAspectRatio: false         
        }
      };

    const chart = new Chart(ctx, config)    
    charts['location'] = chart

}

function setPagesChart(jsonData) {
    const ctx = document.getElementById('pages-chart');

    fillTable(jsonData.data,'pages')

    const labels = Object.keys(jsonData.data)  
    const values = Object.values(jsonData.data)
    const colorsAraay = getColorsArray(labels.length)
    const data = {
        labels: labels,
        datasets: [{
            label: 'Pages',
            data: values,
            fill: false,
            backgroundColor: colorsAraay,
            borderColor: backgroundColor,
            tension: 0.1
        }]
    };    
    const config = {
        type: 'doughnut',
        data: data,
        options: {
            plugins: {
                legend: {
                    display: false,
                },
            },
            responsive: true,
            maintainAspectRatio: false         
        }
      };

    const chart = new Chart(ctx, config)    
    charts['location'] = chart    
    
}

function getColors() {
    const style = getComputedStyle(document.body)
    window.baseColor = style.getPropertyValue('--bulma-link')
    window.secondColor = style.getPropertyValue('--bulma-primary')
    window.backgroundColor = style.getPropertyValue('--bulma-background')
}

const toHSLArray = hslStr => hslStr.match(/\d+/g).map(Number);

function getColorsArray(size) {
    gradientValuesStart = toHSLArray(baseColor)
    gradientValuesEnd = toHSLArray(secondColor)
    outArray = []
    for(let i = 0; i < size; i++){
        if(i == 0) {
            outArray[0] = 'hsl(' + gradientValuesStart[0] + 'deg,' + gradientValuesStart[1] + '%,' + gradientValuesStart[2] + '%)'
            continue
        }
        if(i == size-1 ) {
            outArray[i] = 'hsl(' + gradientValuesEnd[0] + 'deg,' + gradientValuesEnd[1] + '%,' + gradientValuesEnd[2] + '%)'
            continue
        }

        let dH = gradientValuesEnd[0]-gradientValuesStart[0]
        let dS = gradientValuesEnd[1]-gradientValuesStart[1]
        let dL = gradientValuesEnd[2]-gradientValuesStart[2]
        let valH = Math.round(gradientValuesStart[0] + i*(dH/(size-1)))
        let valS = Math.round(gradientValuesStart[1] + i*(dS/(size-1)))
        let valL = Math.round(gradientValuesStart[2] + i*(dL/(size-1)))

        outArray[i] = 'hsl(' + valH + 'deg,' + valS + '%,' + valL + '%)'

    }

    return outArray
}

function toggleTheme() {
    let element = document.querySelector('html')
    let currentTheme = element.getAttribute('data-theme')
    let newTheme = 'light'
    if (currentTheme == 'light') {
        newTheme = 'dark'
    }
    element.setAttribute('data-theme', newTheme)

    // get new color values from css and applay to charts configs
    getColors()
    charts['location'].data.datasets[0].borderColor = backgroundColor
    charts['location'].update()

}
function addSessionRow(data, session) {
    const template = document.getElementById('pages-list-row');
    const clone = document.importNode(template.content, true);
    clone.id = "page-session-" + session;
    clone
      .querySelector('.session-row')
      .setAttribute('id', "page-session-" + session);
    clone.querySelector('.page-url').textContent = data['path'];
    clone.querySelector('.page-date').textContent = data['date'];
    clone.querySelector('.page-browser').textContent = data['browser_name'];
    clone.querySelector('.page-geo').textContent = data['country'] + ' ' + data['city'];

    return clone;
}

function showSessionDetails(data, element) {
    const parent = element.parentElement
    if("data" in data && data['data'].length > 0) {
        data['data'].forEach(entry => {
            parent.appendChild(addSessionRow(entry, data['sessionId']))
        })        
    }
}

function getSessionDetails(event) {
    const sessionDiv = event.currentTarget
    const sessionId = sessionDiv.getAttribute('data-session-id');
    if(sessionId == null) {
        console.log("[ERROR] no session id")
        return
    }

    const existingDetails = document.querySelectorAll("#page-session-" + sessionId)
    if(existingDetails.length > 0) {
        existingDetails.forEach(element => {element.remove()})
        return
    }

    fetch('/esa/api/session/' + sessionId, {
    headers: {
        "Accept": "application/json",
      }
    })
    .then((response) => response.json())
    .then((json) => showSessionDetails(json, sessionDiv))
    .catch((error) => {
        console.error('Error fetching data:', error);
    });    
}

function initLinks() {
    document.querySelectorAll('.session-details').forEach(element => { 
        const counter = element.getAttribute('data-session-counter')
        if(counter > 1) {
            element.addEventListener('click', getSessionDetails)
        }
    })
}

let element = document.querySelector('#theme-toggle');
element.addEventListener("click", toggleTheme, false);

let charts = {}

document.addEventListener("DOMContentLoaded", () => {
    // init our app
    getColors()
    chartInit('visits')
    chartInit('locations')
    chartInit('browsers')
    chartInit('pages')
    initLinks()
})