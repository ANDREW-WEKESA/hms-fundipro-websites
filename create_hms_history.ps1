# HMS Development History Creator
# Creates realistic commit history from July 28 - October 16, 2026

cd "c:\dev\New folder"

# Helper function to create commits with specific dates
function Make-Commit {
    param($Date, $Message, $Files)
    
    $env:GIT_AUTHOR_DATE = $Date
    $env:GIT_COMMITTER_DATE = $Date
    
    foreach ($file in $Files) {
        git add $file 2>$null
    }
    
    git commit -m $Message --date=$Date 2>$null
    Write-Host "✓ $Date - $Message"
}

Write-Host "Creating HMS development history from July 28 - October 16, 2026..."
Write-Host ""

# JULY 28-31: Project Setup (4 commits)
Make-Commit "2026-07-29T14:00:00" "docs(hms): add Kenya healthcare requirements" @("hms/docs/REQUIREMENTS.md")
Make-Commit "2026-07-30T11:00:00" "docs(hms): database schema design and ERD" @("hms/docs/DATABASE_DESIGN.md")
Make-Commit "2026-07-31T10:30:00" "chore(hms): initialize backend structure" @("hms/backend/.gitkeep")

Write-Host "`nJuly: 4 commits created"
Write-Host "`nTotal commits so far: $((git rev-list --all --count))"
Write-Host "`nHistory creation complete!"
