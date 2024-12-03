const express = require('express');
const fs = require('fs');
const bodyParser = require('body-parser');
const { parseString, Builder } = require('xml2js');

const app = express();
const port = 8080;

app.use(express.json());
app.use(express.static('public'));
app.use(bodyParser.text({ type: 'application/xml' }));

app.get('/', (req, res) => {
    res.sendFile(__dirname + '/public/home.php');
});

app.post('/write-contacts', (req, res) => {
    const xmlString = req.body.trim();
    fs.readFile('saved-contacts.xml', 'utf8', (err, data) => {
        if (err) {
            const wrapper = `
<?xml version="1.0" encoding="UTF-8"?>
<contacts>
    ${xmlString}
</contacts>`;

            fs.writeFile('saved-contacts.xml', wrapper.trim(), (err) => {
                if (err) {
                    res.status(500).send("Error writing data to XML");
                }
                else {
                    res.send("Contact saved successfully.");
                }
            });
        }
        else {
            const newContact = data.replace("</contacts>", `\t${xmlString}\n</contacts>`);
            fs.writeFile('saved-contacts.xml', newContact.trim(), (err) => {
                if (err) {
                    res.status(500).send("Error writing data to XML");
                }
                else {
                    res.send("Contact saved successfully.");
                }
            });
        }
    });
});

app.post('/book-hotels', (req, res) => {
    const xmlString = req.body.trim();
    fs.readFile('hotel-bookings.xml', 'utf8', (err, data) => {
        if (err) {
            const wrapper = `
<?xml version="1.0" encoding="UTF-8"?>
<bookings>
    ${xmlString}
</bookings>`;

            fs.writeFile('hotel-bookings.xml', wrapper.trim(), (err) => {
                if (err) {
                    res.status(500).send("Error writing data to XML.");
                }
                else {
                    updateAvailableRooms();
                }
            });
        }
        else {
            const newHotel = data.replace("</bookings>", `\t${xmlString}\n</bookings>`);
            fs.writeFile('hotel-bookings.xml', newHotel.trim(), (err) => {
                if (err) {
                    res.status(500).send("Error writing data to XML.");
                }
                else {
                    updateAvailableRooms();
                }
            });
        }
    });

    function updateAvailableRooms() {
        parseString(xmlString, (err, xmlData) => {
            if (err) {
                return res.status(400).send("Error reading XML data.");
            }

            const hotelId = xmlData['booking']['hotel-id'][0];
            const rooms = parseInt(xmlData['booking']['rooms'][0], 10);

            fs.readFile(__dirname + '/public/hotels-info.json', 'utf-8', (err, jsonData) => {
                if (err) {
                    return res.status(500).send("Error reading hotel info JSON.");
                }
        
                let hotelData;
                try {
                    hotelData = JSON.parse(jsonData);
                } catch (parseErr) {
                    return res.status(500).send("Error parsing hotel info JSON.");
                }
                
                const hotel = hotelData.hotels.find((htl) => htl["hotel-id"] === hotelId);
                if (!hotel) {
                    return res.status(404).send("Hotel not found in JSON data.");
                }
    
                hotel["available-rooms"] -= rooms;
    
                fs.writeFile(__dirname + '/public/hotels-info.json', JSON.stringify(hotelData, null, 4), (err) => {
                    if (err) {
                        return res.status(500).send("Error updating hotel info JSON.");
                    }
                    res.send("Hotel saved and available rooms updated successfully.");
                });
            });
        });
    }
});

app.post('/book-flights', (req, res) => {
    const jsonEntry = req.body;
    fs.readFile('flight-bookings.json', 'utf8', (err, data) => {
        let bookings = [];
        if (!err) {
            bookings = JSON.parse(data);
        }
        bookings.push(jsonEntry);

        fs.writeFile('flight-bookings.json', JSON.stringify(bookings, null, 2), (err) => {
            if (err) {
                res.status(500).send("Error writing data to JSON.");
            }
            
            const seats = jsonEntry.passengers.length;
            const flight1_id = jsonEntry.departing['flight-id'];
            let flight2_id = null;
            if (jsonEntry['trip-type'] === 'round-trip') {
                flight2_id = jsonEntry.returning['flight-id'];
            }

            fs.readFile(__dirname + '/public/flights-info.xml', 'utf-8', (err, xmlData) =>{
                if (err) {
                    return res.status(500).send("Error reading flight info XML.");
                }
                
                parseString(xmlData, (err, flightData) => {
                    if (err) {
                        return res.status(500).send("Error parsing flight info XML.");
                    }
                    
                    const flight1 = flightData["flights"]["flight"].find(flt => flt["flight-id"][0] === flight1_id);
                    const availableSeats1 = parseInt(flight1['available-seats'][0], 10);

                    let flight2 = null;
                    let availableSeats2 = null;
                    if (flight2_id) {
                        flight2 = flightData["flights"]["flight"].find(flt => flt["flight-id"][0] === flight2_id);
                        availableSeats2 = parseInt(flight2['available-seats'][0], 10);
                    }

                    console.log(flight1['available-seats'][0])
                    flight1['available-seats'][0] = (availableSeats1 - seats).toString();
                    console.log(flight1['available-seats'][0])
                    if (flight2 && availableSeats2) {
                        flight2['available-seats'][0] = (availableSeats2 - seats).toString();
                    }

                    const updatedXml = new Builder().buildObject(flightData);

                    fs.writeFile(__dirname + '/public/flights-info.xml', updatedXml, (err) => {
                        if (err) {
                            return res.status(500).send("Error writing updated flight info to XML.");
                        }

                        res.send("Flight booking saved and available seats updated successfully.");
                    });

                });
            })
        });
    });
});

app.listen(port, () => {
    console.log(`Server is running on http://localhost:${port}`);
});