cd .gemini/commands/bmad

# Supprime les références @{...} des fichiers TOML
for file in *.toml; do
  sed -i 's/@{[^}]*}//g' "$file"
done

echo "✓ Cleaned all TOML files"
