import os
import pandas as pd

env = pd.Series(os.environ)
env.sort_index(inplace=True)
print(env)
