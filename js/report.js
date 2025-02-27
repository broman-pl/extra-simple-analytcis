
function visitsChartInit() {
    fetch('/esa/api/visits', {
    headers: {
        "Accept": "application/json",
      }
    })
        .then((response) => response.json())
        .then((json) => setVisitsChart(json));
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
        console.log(isoDate)

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

function getColors() {
    const style = getComputedStyle(document.body)
    window.baseColor = style.getPropertyValue('--bulma-link')
}

function toggleTheme() {
    let element = document.querySelector('html')
    let currentTheme = element.getAttribute('data-theme')
    let newTheme = 'light'
    if (currentTheme == 'light') {
        newTheme = 'dark'
    }
    element.setAttribute('data-theme', newTheme)    
}

let element = document.querySelector('#theme-toggle');
element.addEventListener("click", toggleTheme, false);

document.addEventListener("DOMContentLoaded", () => {
    // init our app
    getColors()
    visitsChartInit()
})