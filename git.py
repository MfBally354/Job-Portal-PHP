#!/usr/bin/env python3

import os
import subprocess
import sys

# ====== WARNA ======
class Colors:
    BLUE = '\033[1;34m'      # BIRU
    GREEN = '\033[1;32m'     # HIJAU
    PINK = '\033[1;35m'      # MERAH MUDA
    DARK_RED = '\033[1;31m'  # MERAH GELAP
    NC = '\033[0m'           # TANPA WARNA

def print_color(text, color):
    """Mencetak teks dengan warna"""
    print(f"{color}{text}{Colors.NC}")

def run_command(command, shell=True):
    """Menjalankan command dan mengembalikan statusnya"""
    try:
        result = subprocess.run(command, shell=shell, check=True, 
                              capture_output=True, text=True)
        return True, result.stdout
    except subprocess.CalledProcessError as e:
        return False, e.stderr

def main():
    MAX_RETRY = 3
    count = 1
    
    print_color("Menambahkan perubahan...", Colors.BLUE)
    success, output = run_command("git add .")
    
    if not success:
        print_color(f"Error saat git add: {output}", Colors.DARK_RED)
        sys.exit(1)
    
    print_color("Melakukan commit...", Colors.BLUE)
    success, output = run_command('git commit -m "Auto commit"')
    
    if not success:
        print_color("Tidak ada perubahan untuk di-commit", Colors.PINK)
    
    while count <= MAX_RETRY:
        print_color(f"Percobaan push ke-{count}...", Colors.BLUE)
        
        success, output = run_command("git push")
        
        if success:
            print_color("✅ Push berhasil!", Colors.GREEN)
            break
        else:
            print_color("❌ Push gagal, mencoba pull --rebase...", Colors.PINK)
            run_command("git pull --rebase")
        
        count += 1
    
    if count > MAX_RETRY:
        print_color(f"🚨 Push gagal setelah {MAX_RETRY} kali percobaan.", Colors.DARK_RED)
    
    # Menampilkan status git
    print("\n=== Status Git ===")
    os.system("git status")

if __name__ == "__main__":
    main()
