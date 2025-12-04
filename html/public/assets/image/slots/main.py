from os import listdir
from os.path import isfile, join

onlyfiles = [f for f in listdir('220-350') if isfile(join(mypath, f))]

print(onlyfiles)