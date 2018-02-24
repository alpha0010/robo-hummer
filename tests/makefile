# Config.

SRCS = marsyasTest.cpp
OUT = marsyasTest

OBJDIR = obj
OUTDIR = bin

CXX = g++
LD = g++

MARSYAS_INSTALL = /home/alpha/dev/marsyas/build/install-spot

INC = -I${MARSYAS_INSTALL}/include
CFLAGS = -Wall -Wextra -std=c++11 -O2
LIB = -lmarsyas
LDFLAGS = -s -Wl,--no-undefined -L${MARSYAS_INSTALL}/lib


# Rules.

OBJS = $(SRCS:%.cpp=$(OBJDIR)/%.o)

all: output_dirs $(OUTDIR)/$(OUT)

help:
	@echo "Targets:"
	@echo "  all     - build the executable"
	@echo "  clean   - clear objects and binaries"
	@echo "  help    - show this message\n"

clean:
	rm -f $(OBJS) $(OUTDIR)/$(OUT)

output_dirs:
	@test -d $(OUTDIR) || { mkdir -p $(OUTDIR); echo mkdir $(OUTDIR); }
	@test -d $(OBJDIR) || { mkdir -p $(OBJDIR); echo mkdir $(OBJDIR); }

$(OUTDIR)/$(OUT): $(OBJS)
	$(LD) -fPIC -o $(OUTDIR)/$(OUT) $(OBJS) $(LDFLAGS) $(LIB)

$(OBJDIR)/%.o: %.cpp
	$(CXX) -MMD -MP -fPIC $(CFLAGS) $(INC) -c $< -o $@

-include $(SRCS:%.cpp=$(OBJDIR)/%.d)

.PHONY: all help clean output_dirs
