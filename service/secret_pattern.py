import hashlib

# Define the secret salt and the string that causes the SQL injection
secret_salt = "secret_salt"
string = "1 OR 1"

# Concatenate the salt and the string
concatenated_string = secret_salt + string

# Calculate the MD5 hash of the concatenated string
md5_hash = hashlib.md5(concatenated_string.encode())

# Convert the MD5 hash to a hexadecimal string, which is the secret pattern
secret_pattern = md5_hash.hexdigest()

# Print the secret pattern
print("The secret pattern is: " + secret_pattern)
