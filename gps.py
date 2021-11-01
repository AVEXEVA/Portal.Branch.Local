import pyautogui
from PIL import Image
import pytesseract
import cv2
import numpy as np
import keyboard
import re
import requests
import matplotlib.pyplot as plt

pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract'

def gps( ):
    image = pyautogui.screenshot()
    width, height = image.size
    image = image.crop( ( width - 450, 20, width, 35 ) )

    pil_image = image
    img = cv2.cvtColor(np.array(pil_image), cv2.COLOR_RGB2BGR)

    hsv = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)

    mask1 = cv2.inRange(hsv, (36, 0, 0), (70, 255,255))

    mask2 = cv2.inRange(hsv, (15,0,0), (36, 255, 255))

    mask = cv2.bitwise_or(mask1, mask2)
    target = cv2.bitwise_and(img,img, mask=mask)

    coordinates = pytesseract.image_to_string( target )
    
    coordinates = coordinates.rpartition(']')[0] + ']'

    if( re.match( 'Position [[][0-9]*[.,][0-9]*[,] [0-9]*[.,][0-9]*[,] [0-9]*[.,][0-9]*]', coordinates ) ):
        coordinates = re.sub( r'([0-9])[.,]+([0-9])', r'\1.\2', coordinates )
        print( coordinates )
        xyz = coordinates[10:].rpartition(']')[0]
        xyz = xyz.split( ',' )
        x, y, z = xyz[0], xyz[1], xyz[2]
        print( 'X = ' + x )
        print( 'Y = ' + y )
        print( 'Z = ' + z )
        dt = {
            'User':1,
            'Token':'e3a44664da1f7bcf60a0d8ef0b64048468eb261edb801420e6930df9827be945',
            'X': int( round( float( x ) / 64 ) ) - 1,
            'Y': ( round( abs( ( float( y ) - 4250 ) - 10000 ) / 64 ) ),
            'Z': 30,
            'Type' : 'Character',
            'Overlay' : 1,
            'TX' : ( ( float( x ) / 64 ) % 1 ) * 256, 
            'TY' : ( ( abs( ( float( y ) - 4250 ) - 10000 ) / 64 ) % 1 ) * 256
        }
        response = requests.post('http://134.209.126.151/bin/php/post/insertMarker.php', data = dt )
        print( dt )
        print( response.content )
    else :
        print( 'bad : ' + coordinates )

while True:
    gps()
    try:
        if keyboard.is_pressed('f9'):
            break  
    except:
        break
